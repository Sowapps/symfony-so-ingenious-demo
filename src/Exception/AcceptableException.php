<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Exception;

use Exception;

/**
 * The data are not processable but this is an expected case (like an empty row in a CSV file)
 */
class AcceptableException extends Exception {

}
