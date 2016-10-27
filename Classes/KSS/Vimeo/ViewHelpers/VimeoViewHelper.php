<?php
namespace KSS\Vimeo\ViewHelpers;

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper as AbstractViewHelper;
use TYPO3\Flow\Annotations as Flow;

/**
 * Vimeo ViewHelper
 *
 * = Examples =
 *
 * <kss:vimeo elementIds="{elementIds}" type="albums" sortBy="manual" />
 */
class VimeoViewHelper extends AbstractViewHelper
{


    /**
     * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
     * @see AbstractViewHelper::isOutputEscapingEnabled()
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Defines the size of the thumbnail. Possible values: 0 - 5 (6 for plus/pro accounts)
     *
     * @var integer
     */
    private $vimeoThumbnailSize = 2;

    /**
     * @var \KSS\Vimeo\Utility\Umlaute
     * @Flow\Inject
     */
    protected $umlaute;


    /**
     * ViewHelper that generates the video list
     *
     * @param array elements The id for an e.g. albums
     * @param integer $thumbnailSize Size of the thumbnails which should be used. Possible values: 0 - 5 (6 for plus/pro accounts)
     * @param boolean $showFilter Display filters above the thumbnails
     * @param string $videosPerRowExtendedDesktop Amount of videos in each row for breakpoint "extended desktop"
     * @param string $videosPerRowDesktop Amount of videos in each row for breakpoint "desktop"
     * @param string $videosPerRowTablet Amount of videos in each row for breakpoint "tablet"
     * @param string $videosPerRowMobile Amount of videos in each row for breakpoint "mobile"
     * @param string $defaultStartFilter The selected filter when isotope is loaded
     * @param boolean $addAllFilter Whether the "all" filter should be added or not
     *
     * @return string
     */
    public function render(array $elements, $thumbnailSize = 2, $showFilter = true, $videosPerRowExtendedDesktop = 'seven', $videosPerRowDesktop = 'five', $videosPerRowTablet = 'three', $videosPerRowMobile = 'one', $defaultStartFilter = '*', $addAllFilter = true)
    {
        $this->vimeoThumbnailSize = $thumbnailSize;
        $output                   = '';

        if ($showFilter && $elements['type'] != 'albumSingle') {
            $output .= $this->addFilter($elements['data'], $defaultStartFilter, $addAllFilter);
        }

        $output .= '<div class="vimeo-container row" id="vimeo-grid">';

        if ($elements['type'] == 'videos') {
        } else {


            // Check if any videos are available
            if (count($elements['data']) == 0) {
                $output = '<div class"bg-info">';
                $output .= '<h3 style="font-weight: bold">No ' . $elements['type'] . ' available!</h3>';
                $output .= '</div></div>';

                return $output;
            } else {
                if ($elements['type'] == 'albumSingle') {
                    $sizes = count($elements['data']);
                } else {
                    $sizes = count($elements['data'][0]['videos'][0]['pictures']['sizes']) - 1;
                }
            }

            // check if valid thumbnailsize is selected
            if ($this->vimeoThumbnailSize > $sizes || $this->vimeoThumbnailSize < 0) {
                $output = '<div class"bg-warning">';
                $output .= '<h3 style="font-weight: bold">Invalid thumbnail size! Only vimeo plus/pro accounts have full HD!</h3>';
                $biggestThumbnailSize = array_pop($elements[0]['pictures']['sizes']);
                $output .= '<p>Your maximal available thumbnail size is: ' . $biggestThumbnailSize['width'] . 'x' . $biggestThumbnailSize['height'] . 'px</p>';
                $output .= '</div>';
            }


            $output .= '<div id="vimeo-grid-spacer" class="item col-xs-' . $videosPerRowMobile . ' col-sm-' . $videosPerRowTablet . ' col-md-' . $videosPerRowDesktop . ' col-lg-' . $videosPerRowExtendedDesktop . '"></div>';
            $output .= '<script>window.onedropVimeoGridData = {';
            $output .= 'itemClasses: "item col-xs-' . $videosPerRowMobile . ' col-sm-' . $videosPerRowTablet . ' col-md-' . $videosPerRowDesktop . ' col-lg-' . $videosPerRowExtendedDesktop . '",';
            $output .= 'items: [';

            // Build for each returned json object the html
            $distinctVideos = [];

            if ($elements['type'] == 'albumSingle') {
                foreach ($elements['data'] as $video) {
                    $video['albums']                  = ['albumSingle'];
                    $distinctVideos[ $video['link'] ] = $video;
                }
            } else {
                foreach ($elements['data'] as $element) {
                    foreach ($element['videos'] as $video) {
                        if (array_key_exists($video['link'], $distinctVideos)) {
                            $distinctVideos[ $video['link'] ]['albums'][] = $element['name'];
                        } else {
                            $video['albums']                  = [ $element['name'] ];
                            $distinctVideos[ $video['link'] ] = $video;
                        }
                    }
                }
            }

            foreach ($distinctVideos as $video) {
                $output .= $this->createJsonObject($video) . ',';
            }

            $output .= ']}</script>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Build build a json object from video data
     *
     * @param string $data The video
     *
     * @return string return all videos as html elements
     */
    public function createJsonObject($data)
    {
        $object = [
            'albums'      => [],
            'releaseTime' => $data['release_time'],
            'title'       => $data['name'],
            'link'        => $data['link'],
            'thumbnail'   => [
                'link'   => $data['pictures']['sizes'][ $this->vimeoThumbnailSize ]['link'],
                'width'  => $data['pictures']['sizes'][ $this->vimeoThumbnailSize ]['width'],
                'height' => $data['pictures']['sizes'][ $this->vimeoThumbnailSize ]['height']
            ]
        ];
        foreach ($data['albums'] as $album) {
            $object['albums'][] = [
                'ID'    => $this->umlaute->convertAccentAndBlankspace($album),
                'title' => $album
            ];
        }

        return json_encode($object);
    }

    /**
     * Create filter buttons
     *
     * @param array $elementNames
     * @param string $defaultStartFilter
     * @param boolean $addAllFilter
     *
     * @return string
     */
    public function addFilter($elementNames, $defaultStartFilter, $addAllFilter)
    {
        // All filter is not allowed to have . infront of *
        if ($defaultStartFilter == '' || $defaultStartFilter == '*') {
            $html = '<div id="vimeo-filters" class="button-group" data-default-start-filter="*">';
        } else {
            $html = '<div id="vimeo-filters" class="button-group" data-default-start-filter="' . '.' . $defaultStartFilter . '">';
        }
        // check if "all" filter should be displayed
        if ($addAllFilter === true) {
            $html .= '<button data-filter="*">All</button>';
        }

        if (count($elementNames) > 0) {
            foreach ($elementNames as $filter) {
                $html .= '<button data-value="' . $this->umlaute->convertAccentAndBlankspace($filter['name']) . '">' . $filter['name'] . '</button>';
            }
        }

        $html .= '</div>';

        return $html;
    }
}
