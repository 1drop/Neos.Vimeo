<?php
namespace KSS\Vimeo\Services;

/*                                                                        *
 * This script belongs to the Flow package "Neos.Kickstarter".            *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;


/**
 * Service to generate site packages
 */
class VimeoGetAllOfTypeService {

    /**
     * @var \Vimeo\Vimeo
     */
    protected $vimeo;

    /**
     * Vimeo authentication function
     *
     * @param $clientId
     * @param $clientSecret
     * @param $accessToken
     *
     * @return \Vimeo\Vimeo
     */
    private function authenticate_vimeo( $clientId, $clientSecret, $accessToken ) {
        // Do an authentication call
        $this->vimeo = new \Vimeo\Vimeo( $clientId, $clientSecret, $accessToken );
    }

    /**
     * Get all elements of the authenticated user
     *
     * @param $userID
     * @param $clientId
     * @param $clientSecret
     * @param $accessToken
     * @param string $type
     * @param string $sortVideosBy
     * @param string $sortVideoDirection
     * @param string $sortTypeBy
     * @param string $sortTypeDirection
     * @param string $privacyOfType
     *
     * @return mixed
     */
    public function getAllOfType( $userID = 'me', $clientId, $clientSecret, $accessToken, $type = 'albums', $sortVideosBy = 'alphabetical', $sortVideoDirection = 'asc', $sortTypeBy = 'alphabetical', $sortTypeDirection = 'asc', $privacyOfType = 'anybody' ) {
        $this->authenticate_vimeo( $clientId, $clientSecret, $accessToken );

        // Set url options and make request to vimeo
        $urlOptionsString = '/users/' . $userID . '/' . $type;
        $result           = $this->vimeo->request( $urlOptionsString, array(
            'per_page'  => 50,
            'sort'      => $sortTypeBy,
            'direction' => $sortTypeDirection
        ) );

        $nextPage        = true;
        $element         = [];
        $element['data'] = [];
        $i               = 0;

        // iterate through all pages
        while ( $nextPage ) {
            // check for error message, if so append
            $element['status'] = $result['status'];
            if ( $element['status'] != 200 ) {
                $element['message'] = $result['body']['error'];
                $nextPage           = false;
            } else {
                // Add selected type to array
                $element['type'] = $type;

                // Loop through each data set of the result and build new simple array to return
                foreach ( $result['body']['data'] as $resultObject ) {
                    // check for correct privacy level of type
                    if ( $privacyOfType == 'all' || $privacyOfType == $resultObject['privacy']["view"] ) {
                        $element['data'][ $i ]['name']     = $resultObject['name'];
                        $element['data'][ $i ]['uri']      = $resultObject['uri'];
                        $element['data'][ $i ]['link']     = $resultObject['link'];
                        $element['data'][ $i ]['privacy']  = $resultObject['privacy'];
                        $element['data'][ $i ]['pictures'] = $resultObject['pictures'];

                        // No extra request necessary for type videos to fetch all videos from vimeo
                        if ( $type != 'videos' ) {
                            $videos                          = $this->getAllVideosOfElement( $resultObject['uri'], $sortVideosBy, $sortVideoDirection );
                            $element['data'][ $i ]['videos'] = $videos['body']['data'];
                        }

                        $i ++;
                    }
                }

                if ( $result['body']['paging'] && $result['body']['paging']['next'] ) {
                    $result = $this->vimeo->request( $result['body']['paging']['next'] );
                } else {
                    $nextPage = false;
                }
            }
        }

        return $element;
    }

    /**
     * Get all videos to given link
     *
     * @param $link link to the element
     *
     * @return array result of request (found videos)
     */
    public function getAllVideosOfElement( $link, $sortVideosBy, $sortVideoDirection ) {
        return $this->vimeo->request( $link . '/videos', array(
            'sort'      => $sortVideosBy,
            'direction' => $sortVideoDirection
        ) );
    }


}
