<?php

if(!file_exists('../../../app/Helper'))
	throw new exception('Target directory doesn\'t exists');

if(!copy('./src/Helper/BaseHelper.php', '../../../app/Helper/BaseHelper.php'))
	throw new exception('Copy failed');