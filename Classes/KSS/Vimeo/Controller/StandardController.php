<?php
namespace KSS\Vimeo\Controller;

/*                                                                        *
 * This script belongs to the Flow package "KSS.Vimeo".                   *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController
{

    /**
     * @Flow\Inject
     * @var \KSS\Vimeo\Services\VimeoGetAllOfTypeService
     */
    protected $vimeoGetAllOfTypeService;

    /**
     * @return void
     */
    public function indexAction()
    {

        // necessary for user auth
        $userId = $this->request->getInternalArgument('__userId');
        if ($userId == '') {
            $userId = 'me';
        }
        $client_id = $this->request->getInternalArgument('__clientId');
        $client_secret = $this->request->getInternalArgument('__clientSecret');
        $access_token = $this->request->getInternalArgument('__accessToken');

        // necessary to fetch data
        $vimeoType = $this->request->getInternalArgument('__vimeoType');
        $albumSingleId = $this->request->getInternalArgument('__albumSingleId');
        $sortVideosBy = $this->request->getInternalArgument('__sortVideosBy');
        $sortVideosDirection = $this->request->getInternalArgument('__sortVideosDirection');
        $sortTypeBy = $this->request->getInternalArgument('__sortTypeBy');
        $sortTypeDirection = $this->request->getInternalArgument('__sortTypeDirection');
        $privacyOfType = $this->request->getInternalArgument('__privacyOfType');

        // necessary to config viewhelper
        $thumbnailSize = $this->request->getInternalArgument('__thumbnailSize');
        $showFilter = $this->request->getInternalArgument('__showFilter');
        $defaultStartFilter = $this->request->getInternalArgument('__defaultStartFilter');
        $addAllFilter = $this->request->getInternalArgument('__addAllFilter');
        $useVisualHelperLibrary = $this->request->getInternalArgument('__useVisualHelperLibrary');
        $videosPerRowExtendedDesktop = $this->request->getInternalArgument('__videosPerRowExtendedDesktop');
        $videosPerRowDesktop = $this->request->getInternalArgument('__videosPerRowDesktop');
        $videosPerRowTablet = $this->request->getInternalArgument('__videosPerRowTablet');
        $videosPerRowMobile = $this->request->getInternalArgument('__videosPerRowMobile');
        $loadMoreButtonText = $this->request->getInternalArgument('__loadMoreButtonText');
        $node = $this->request->getInternalArgument('__node');

        $allElementsOfUser = $this->vimeoGetAllOfTypeService->getAllOfType($userId, $client_id, $client_secret, $access_token, $vimeoType, $albumSingleId, $sortVideosBy, $sortVideosDirection, $sortTypeBy, $sortTypeDirection, $privacyOfType);

        $this->view->assignMultiple([
            'node' => $node,
            'loadMoreButtonText' => $loadMoreButtonText,
            'albumSingleId' => $albumSingleId,
            'allElementsOfUser' => $allElementsOfUser,
            'thumbnailSize' => $thumbnailSize,
            'showFilter' => $showFilter,
            'useVisualHelperLibrary' => $useVisualHelperLibrary,
            'videosPerRowExtendedDesktop' => $videosPerRowExtendedDesktop,
            'videosPerRowDesktop' => $videosPerRowDesktop,
            'videosPerRowTablet' => $videosPerRowTablet,
            'videosPerRowMobile' => $videosPerRowMobile,
            'defaultStartFilter' => $defaultStartFilter,
            'addAllFilter' => $addAllFilter
        ]);
    }
}
