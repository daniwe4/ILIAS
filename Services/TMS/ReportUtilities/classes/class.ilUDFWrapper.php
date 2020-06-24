<?php declare(strict_types=1);

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

/**
 * Provide access to ILIAS' UDFs.
 *
 * getFieldsVisibleInCourseMemberAdministration will return a dict (field_id=>[internal_name, field_name]) of UDFs.
 * To use this, setup your space and append UDFs with appendUDFsToSpace();
 * this will extend $space with a DerivedTable (id='udf').
 * Example:
 *		$space = $this->appendUDFsToSpace(
 *			$this->tf, $this->pf,
 *			$space, $usr_data, 'usr_id'
 *		);
 *
 * Then add the udf-colums to the tableGUI:
 * 		$table = $this->addUDFColumnsToTable($space, $table);
 *
 * Finally, you will have to adjust the row-template by adding a generic block like this:
 *		<!-- BEGIN udf_block -->
 *			<td>{VAL_UDFFIELD}</td>
 *		<!-- END udf_block -->
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-traning.de>
 */
trait ilUDFWrapper {

	/**
	 * Add UDF-columns to the table
	 *
	 * @param TableRelations\Tables\TableSpace 	$space
	 * @param \SelectableReportTableGUI 		$table
	 * @return \SelectableReportTableGUI
	 */
	protected function addUDFColumnsToTable(
		TableRelations\Tables\TableSpace $space,
		\SelectableReportTableGUI $table
	) {
		$il_udf_definitions = $this->getFieldsVisibleInCourseMemberAdministration();
		foreach ($il_udf_definitions as $field_id => list($internal_name, $field_name)) {
			$col_id = 'UDF_' .(string)$field_id;
			$table = $table->defineFieldColumn(
				$field_name,
				$col_id,
				[$col_id => $space->table('udf')->field($internal_name)]
				,true
			);
		}
		return $table;
	}

	/**
	 * Add UDFs to "master"-space and hook to a table/field providing the user's id.
	 *
	 * @param TableRelations\TableFactory 		$tf
	 * @param Filter\PredicateFactory 			$pf
	 * @param TableRelations\Tables\TableSpace 	$space
	 * @param TableRelations\Tables\Table 		$usr_table
	 * @param string 							$usr_id_field_name
	 * @return TableRelations\Tables\TableSpace
	 */
	protected function appendUDFsToSpace(
		TableRelations\TableFactory $tf,
		Filter\PredicateFactory $pf,
		TableRelations\Tables\TableSpace $space,
		TableRelations\Tables\Table $usr_table,
		string $usr_id_field_name
	) {
		$udf_table = $this->buildBasicUDFTable($tf, $pf);
		$eq_field = $usr_table->field($usr_id_field_name);
		$space = $space
			->addTableSecondary($udf_table)
			->addDependency(
				$tf->TableLeftJoin(
					$usr_table,
					$udf_table,
					$udf_table->field('usr_id')->EQ($eq_field)
				)
			);
		return $space;
	}

	/**
	 * Create a table representing visible user data fields.
	 *
	 * @param	TableRelations\TableFactory	$tf
	 * @param	string	$table_alias
	 * @return	TableRelations\Tables\Table
	 */
	protected function configuredUserDataTable(
		TableRelations\TableFactory $tf,
		string $table_alias
	) : TableRelations\Tables\Table
	{
		$usr_data = $tf->Table('usr_data', $table_alias)
					->addField($this->tf->field('usr_id'));
		foreach ($this->getAllCourseVisibleStandardUserFields() as $field) {
			if ($field == "username") {
				$field = "login";
			}

			// skip orgunits. this field will be dealt with elsewhere
			if ($field == "org_units") {
				continue;
			}
			$usr_data = $usr_data->addField($this->tf->field($field));
		}
		return $usr_data;
	}

	/**
	 * Setup a SelectableTableGUI to show visible standard usr-data field.
	 *
	 * @param	TableRelations\Tables\TableSpace	$space
	 * @param	\SelectableReportTableGUI	$gui_table
	 * @param	string	$table_alias
	 * @return	SelectableReportTableGUI
	 */
	protected function addUserDataToTable(
		TableRelations\Tables\TableSpace $space,
		\SelectableReportTableGUI $gui_table,
		string $table_alias
	) : \SelectableReportTableGUI
	{
		global $DIC;
		$lng = $DIC['lng'];
		foreach ($this->getAllCourseVisibleStandardUserFields() as $field) {

			$field_name = $lng->txt($field);
			if ($field == "username") {
				$field = "login";
			}

			// skip orgunits. this field will be dealt with elsewhere
			if ($field == "org_units") {
				continue;
			}
			$col_id = $this->usrDataFieldId($field);
			$gui_table = $gui_table->defineFieldColumn(
				$field_name,
				$col_id,
				[$col_id => $space->table($table_alias)->field($field)]
				,true
			);
		}
		return $gui_table;
	}

	/**
	 * normalized presentation field-id for usr-data fields.
	 *
	 * @param	string	$table_alias
	 * @param	string	$field_id
	 * @return	string
	 */
	protected function usrDataFieldId(
		string $field_id
	) : string
	{
		return 'standard_user_data_presentation_'.$field_id;
	}

	/**
	 * Get all standard user fields visible in Courses
	 *
	 * @return string[]
	 */
	protected function getAllCourseVisibleStandardUserFields()
	{
		include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
		$ef = \ilExportFieldsInfo::_getInstanceByType("crs");

		return $ef->getExportableFields();
	}




	/**
	 * Get the fields visible in local user administration.
	 *
	 * @return array<int, string> 	field_id=>field_name
	 */
	protected function getFieldsVisibleInCourseMemberAdministration() {
		require_once 'Services/User/classes/class.ilUserDefinedFields.php';
		$il_udf = \ilUserDefinedFields::_getInstance();
		$ret = [];
		foreach ($il_udf->getCourseExportableFields() as $udf_def) {
			//$ret[$udf_def['field_id']] = $this->sanitizeUDFName($udf_def['field_name']);
			$ret[$udf_def['field_id']] = [
					'i'.$udf_def['field_id'], //name used internally
					$udf_def['field_name'] //clear name
				];
		}
		return $ret;
	}

	/**
	 * Setup Space/Table for UDF
	 *
	 * @return TableRelations\Tables\DerivedTable
	 */
	private function buildBasicUDFTable(
		TableRelations\TableFactory $tf,
		Filter\PredicateFactory $pf
	) {
		//basic UDF setup
		$udf_def = $tf->Table('udf_definition', 'udf_def')
			->addField($tf->field('field_id'))
			->addField($tf->field('field_name'));

		$udf_txt = $tf->Table('udf_text', 'udf_txt')
			->addField($tf->field('usr_id'))
			->addField($tf->field('field_id'))
			->addField($tf->field('value'));

		$udf_space = $tf->TableSpace()
			->addTablePrimary($udf_txt)
			->addTablePrimary($udf_def)
			->setRootTable($udf_txt)
			->addDependency($tf->TableLeftJoin($udf_txt, $udf_def, $udf_def->field('field_id')->EQ($udf_txt->field('field_id'))))
			->request($udf_txt->field('usr_id'));

		// create fields;
		// build a query like this to "pivot" udf-text:
		// SELECT  udf_txt.usr_id AS usr_id,
		//     MAX( IF(`udf_txt`.`field_id` = 1 , udf_txt.value,0)) AS UDF_1
		// FROM udf_text AS udf_txt
		//     LEFT JOIN udf_definition AS udf_def ON (`udf_def`.`field_id` = `udf_txt`.`field_id` )
		// GROUP BY udf_txt.usr_id

		$udf_nullfield = $tf->constString('nullval', '');
		$udf_fields = [];
		foreach ($this->getFieldsVisibleInCourseMemberAdministration() as $udf_field_id => list($udf_internal_name, $udf_field_name)) {
			$udf_fid = $pf->int($udf_field_id);
			$udf_fields[] = $tf->max(
				$udf_internal_name,
				$tf->ifThenElse(
					'internal_udf_'.$udf_field_id, //name
					$udf_txt->field('field_id')->EQ($udf_fid),
					$udf_txt->field('value'),
					$udf_nullfield
				)
			);
		}

		//apply to space
		foreach ($udf_fields as $udf_field) {
			$udf_space = $udf_space->request($udf_field);
		}
		$udf_space = $udf_space->groupBy($udf_txt->field('usr_id'));
		$udf_table = $tf->derivedTable($udf_space, 'udf');

		return $udf_table;
	}
}
