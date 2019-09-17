<?php

use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\ActionBuilderBase;
use PHPUnit\Framework\TestCase;

class ActionBuilderTest extends TestCase
{
    public function test_entity()
    {
        $entity = $this->createMock(CaT\Ente\Entity::class);
        $owner = $this->createMock(ilObject::class);
        $user = $this->createMock(\ilObjUser::class);

        $builder = new ActionBuilderBase($entity, $owner, $user);
        $this->assertSame($entity, $builder->entity());
    }

    /**
     * @dataProvider contextProvider
     */
    public function test_call_functions_by_context($context, $fnc)
    {
        $builder = $this->getMockBuilder(ActionBuilderBase::class)
            ->setMethods([$fnc, "sortActionsByPriority", "filterActionsByUserId"])
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock()
        ;

        $actions = [];
        $builder->expects($this->once())
            ->method($fnc)
            ->willReturn($actions)
        ;

        $builder->expects($this->once())
            ->method("sortActionsByPriority")
            ->with($actions)
            ->willReturn($actions)
        ;

        $builder->expects($this->once())
            ->method("filterActionsByUserId")
            ->with($actions, 1)
            ->willReturn($actions)
        ;

        $builder->getCourseActionsFor($context, 1);
    }

    public function contextProvider()
    {
        return [
            [ActionBuilderBase::CONTEXT_SEARCH, "getSearchActions"],
            [ActionBuilderBase::CONTEXT_USER_BOOKING, "getUserBookingActions"],
            [ActionBuilderBase::CONTEXT_EMPLOYEE_BOOKING, "getEmployeeBookingActions"],
            [ActionBuilderBase::CONTEXT_EDU_BIO, "getEduBiographyActions"],
            [ActionBuilderBase::CONTEXT_EMPOYEE_EDU_BIO, "getEmployeeEduBiographyActions"],
            [ActionBuilderBase::CONTEXT_MY_TRAININGS, "getMyTrainingActions"],
            [ActionBuilderBase::CONTEXT_MY_ADMIN_TRAININGS, "getMyAdministratedTrainingActions"],
            [ActionBuilderBase::CONTEXT_SUPERIOR_SEARCH, "getSuperiorSearchActions"],
            [ActionBuilderBase::CONTEXT_TEP_SESSION_DETAILS, "getTepSessionDetailActions"]
        ];
    }
}
