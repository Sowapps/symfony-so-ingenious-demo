<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Test;

class TestEntity {
	
	private int $avatar = -1;
	
	/**
	 * @return int
	 */
	public function getAvatar(): int {
		return $this->avatar;
	}
	
	/**
	 * @param int $avatar
	 */
	public function setAvatar(int $avatar): void {
		$this->avatar = $avatar;
	}
	
	
}
