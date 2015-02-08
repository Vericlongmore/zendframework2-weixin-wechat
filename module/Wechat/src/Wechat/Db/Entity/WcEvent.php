<?php

namespace Wechat\Db\Entity;

use Wechat\Db\DbArrayObject;

class WcEvent extends DbArrayObject {
	const TABLE = 'wc_event';
	const EID = 'eid';
	const EVENT = 'event';
	const EVENT_KEY = 'eventKey';
	const TICKET = 'ticket';
	const LATITUDE = 'latitude';
	const LONGITUDE = 'longitude';
	const PRECISION = 'precision';
}