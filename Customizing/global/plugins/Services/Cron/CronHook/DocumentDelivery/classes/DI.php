<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery;

use Pimple\Container;

trait DI
{
	public function getPluginDIC(
		\ilDocumentDeliveryPlugin $plugin,
		\ArrayAccess $dic
	): Container {
		$container = new Container();

		$container["ilDB"] = function($c) use ($dic) {
			return $dic["ilDB"];
		};

		$container["lng"] = function($c) use ($dic) {
			return $dic["lng"];
		};

		$container["signaturelist.db"] = function($c) {
			return new SignatureList\ilDB(
				$c["ilDB"]
			);
		};

		$container['signaturelist.print'] = function($c) {
			return new SignatureList\Printer(
				$c["lng"]
			);
		};

		$container["documents.db"] = function($c) {
			return new Documents\ilDB(
				$c["ilDB"]
			);
		};

		return $container;
	}
}
