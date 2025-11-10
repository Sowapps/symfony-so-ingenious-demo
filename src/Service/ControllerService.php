<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

class ControllerService {

    public function __construct(
        public readonly ClientSessionService $clientSessionService,
        public readonly EntityService        $entityService,
        public readonly SecurityService      $securityService,
    ) {
    }

}
