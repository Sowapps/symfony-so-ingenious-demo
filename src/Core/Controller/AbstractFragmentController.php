<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Core\Controller;

use App\Entity\Fragment;
use App\Service\FragmentService;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Sowapps\SoCore\Service\SecurityService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

class AbstractFragmentController extends AbstractController {

    protected FragmentService $fragmentService;
    protected SecurityService $securityService;

    public function renderFragment(Fragment $fragment, bool $editor, array $parameters = []): Response {
        // ADOV or ADOX, they exclude each other
        // ADOV: ADmin OVerlay to edit website content
        // ADOX: ADmin toolbOX to manage features on public pages
        if( $editor ) {
            // Check permissions, require firewall config lazy: false
            $this->denyAccessUnlessGranted(SecurityService::ROLE_CONTRIBUTOR);
            $parameters['enableAdov'] = true;
            $parameters['enableAdox'] = false;
        } else {
            $parameters['enableAdov'] = false;
            $parameters['enableAdox'] = $this->isGranted(SecurityService::ROLE_CONTRIBUTOR);
        }
        return new Response($this->fragmentService->getFragmentRendering($fragment, $parameters));
    }

    #[Required]
    public function initializeAbstractFragmentController(
        FragmentService $fragmentService,
    ): static {
        $this->fragmentService = $fragmentService;

        return $this;
    }

}
