<?php
namespace KSS\Vimeo\DataSource;

use TYPO3\Neos\Service\DataSource\AbstractDataSource;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Flow\Annotations as Flow;

class FilterOptionDataSource extends AbstractDataSource {

	/**
	 * @Flow\Inject
	 * @var \KSS\Vimeo\Services\VimeoGetAllOfTypeService
	 */
	protected $vimeoGetAllOfTypeService;

	/**
	 * @var \KSS\Vimeo\Utility\Umlaute
	 * @Flow\Inject
	 */
	protected $umlaute;

	/**
	 * @var string
	 */
	static protected $identifier = 'kss-vimeo-filteroption';

	/**
	 * Get data
	 *
	 * @param NodeInterface $node The node that is currently edited (optional)
	 * @param array $arguments Additional arguments (key / value)
	 * @return array JSON serializable data
	 */
	public function getData(NodeInterface $node = NULL, array $arguments) {

		// necessary for user auth
		$userId = $node->getProperty('userId');
		if($userId == '') $userId = 'me';
		$client_id = $node->getProperty('clientId');
		$client_secret = $node->getProperty('clientSecret');
		$access_token = $node->getProperty('accessToken');

		// necessary to fetch data
		$vimeoType = $node->getProperty('vimeoType');
		$sortVideosBy = $node->getProperty('sortVideosBy');
		$sortVideosDirection = $node->getProperty('sortVideosDirection');
		$sortTypeBy = $node->getProperty('sortTypeBy');
		$sortTypeDirection = $node->getProperty('sortTypeDirection');
		$privacyOfType = $node->getProperty('privacyOfType');

		$allElementsOfUser = $this->vimeoGetAllOfTypeService->getAllOfType($userId,$client_id, $client_secret, $access_token, $vimeoType, $sortVideosBy, $sortVideosDirection, $sortTypeBy, $sortTypeDirection, $privacyOfType);

		$result = [];

		// check if at least one element exist, if so create the array for the answer
		if(count($allElementsOfUser['data']) > 0){
			// create array for dynamic options
			foreach($allElementsOfUser['data'] as $filter){
				$key = $this->umlaute->convertAccentAndBlankspace($filter['name']);
				$result[$key] = [
					"label" => $filter['name'],
					#"group" => "filter",
					#"icon" => "icon-angle-right"
				];
			}
		}else{
			$result['empty'] = [
				"label" => "no data available"
			];
		}

		ksort($result);

		return $result;
	}

}