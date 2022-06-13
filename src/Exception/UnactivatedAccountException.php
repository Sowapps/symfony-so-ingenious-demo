<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;


class UnactivatedAccountException extends AccountStatusException {
	
	public function getMessageKey() {
		return 'user.login.unactivated';
	}
	
}
