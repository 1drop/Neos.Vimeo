<?php
namespace KSS\Vimeo\Services;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos.Kickstarter".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;


/**
 * Service to generate site packages
 */
class VimeoGetAllOfTypeService{

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
	 * @return \Vimeo\Vimeo
	 */
	private function authenticate_vimeo($clientId, $clientSecret, $accessToken){
		// Do an authentication call
		$this->vimeo = new \Vimeo\Vimeo($clientId, $clientSecret, $accessToken);
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
	 * @return mixed
	 */
	public function getAllOfType($userID = 'me', $clientId, $clientSecret, $accessToken, $type='albums', $sortVideosBy='alphabetical',$sortVideoDirection='asc', $sortTypeBy='alphabetical' ,$sortTypeDirection = 'asc', $privacyOfType = 'anybody'){
		$this->authenticate_vimeo($clientId, $clientSecret, $accessToken);

		// Set url options and make request to vimeo
		$urlOptionsString = '/'.$userID.'/'.$type;
		$result = $this->vimeo->request($urlOptionsString,array('sort'=>$sortTypeBy,'direction'=>$sortTypeDirection));

		$element = [];
		$element['data'] = [];

		// check for error message, if so append
		$element['status'] = $result['status'];
		if($element['status'] != 200){
			$element['message'] = $result['body']['error'];
		}else{
			// Add selected type to array
			$element['type'] = $type;

			// Loop through each data set of the result and build new simple array to return
			$i = 0;
			foreach($result['body']['data'] as $type){
				// check for correct privacy level of type
				if($privacyOfType == 'all' || $privacyOfType == $type['privacy']["view"]){
					$element['data'][$i]['name'] = $type['name'];
					$element['data'][$i]['uri'] = $type['uri'];
					$element['data'][$i]['link'] = $type['link'];
					$element['data'][$i]['privacy'] = $type['privacy'];
					$element['data'][$i]['pictures'] = $type['pictures'];

					// No extra request necessary for type videos to fetch all videos from vimeo
					if($type != 'videos'){
						$videos = $this->getAllVideosOfElement($type['uri'],$sortVideosBy,$sortVideoDirection);
						$element['data'][$i]['videos'] = $videos['body']['data'];
					}

					$i++;
				}
			}
		}


		return $element;
	}

	/**
	 * Get all videos to given link
	 *
	 * @param $link link to the element
	 * @return array result of request (found videos)
	 */
	public function getAllVideosOfElement($link,$sortVideosBy,$sortVideoDirection){
		return $this->vimeo->request($link.'/videos',array('sort'=>$sortVideosBy,'direction'=>$sortVideoDirection));
	}


}