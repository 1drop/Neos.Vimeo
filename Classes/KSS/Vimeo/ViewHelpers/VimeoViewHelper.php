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
class VimeoViewHelper extends AbstractViewHelper {


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
    public function render( array $elements, $thumbnailSize = 2, $showFilter = true, $videosPerRowExtendedDesktop = 'seven', $videosPerRowDesktop = 'five', $videosPerRowTablet = 'three', $videosPerRowMobile = 'one', $defaultStartFilter = '*', $addAllFilter = true ) {


        $this->vimeoThumbnailSize = $thumbnailSize;
        $output                   = '';

        if ( $showFilter ) {
            $output .= $this->addFilter( $elements['data'], $defaultStartFilter, $addAllFilter );
        }

//        \TYPO3\Flow\var_dump( $elements['data'][0] );

        $output .= '<div class="vimeo-container row" id="vimeo-grid">';

        if ( $elements['type'] == 'videos' ) {

        } else {


            // Check if any videos are available
            if ( count( $elements['data'] ) == 0 ) {
                $output = '<div class"bg-info">';
                $output .= '<h3 style="font-weight: bold">No ' . $elements['type'] . ' available!</h3>';
                $output .= '</div></div>';

                return $output;
            } else {
                $sizes = ( count( $elements['data'][0]['videos'][0]['pictures']['sizes'] ) - 1 );
            }

            // check if valid thumbnailsize is selected
            if ( $this->vimeoThumbnailSize > $sizes || $this->vimeoThumbnailSize < 0 ) {
                $output = '<div class"bg-warning">';
                $output .= '<h3 style="font-weight: bold">Invalid thumbnail size! Only vimeo plus/pro accounts have full HD!</h3>';
                $biggestThumbnailSize = array_pop( $elements[0]['pictures']['sizes'] );
                $output .= '<p>Your maximal available thumbnail size is: ' . $biggestThumbnailSize['width'] . 'x' . $biggestThumbnailSize['height'] . 'px</p>';
                $output .= '</div>';
            }

            // Build for each returned json object the html
            foreach ( $elements['data'] as $element ) {
                foreach ( $element['videos'] as $video ) {
                    $output .= $this->createHtmlElement( $video, $element, $videosPerRowMobile, $videosPerRowTablet, $videosPerRowDesktop, $videosPerRowExtendedDesktop );
                }
            }

        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Build the html code for each video
     *
     * @param string $data The video
     * @param string $element This is needed to get the album name for filtering
     * @param integer $videosPerRowMobile
     * @param integer $videosPerRowTablet
     * @param integer $videosPerRowDesktop
     * @param integer $videosPerRowExtendedDesktop
     *
     * @return string return all videos as html elements
     */
    public function createHtmlElement( $data, $element, $videosPerRowMobile, $videosPerRowTablet, $videosPerRowDesktop, $videosPerRowExtendedDesktop ) {
        $html = '';

        // loop through every given video element and create an html item
        $html .= '<div class="item col-xs-'.$videosPerRowMobile.' col-sm-'.$videosPerRowTablet.' col-md-'.$videosPerRowDesktop.' col-lg-'.$videosPerRowExtendedDesktop.'" data-album="' . $this->umlaute->convertAccentAndBlankspace( $element['name'] ) . '" data-release="' . $data['release_time'] . '" data-title="' . $data['name'] . ' - ' . $element['name'] . '">';
        $html .= '<a href="' . $data['link'] . '" target="_self" class="fancybox-media embed-responsive embed-responsive-16by9">';
        $html .= '<img class="embed-responsive-item" src="' . $data['pictures']['sizes'][ $this->vimeoThumbnailSize ]['link'] . '" width="' . $data['pictures']['sizes'][ $this->vimeoThumbnailSize ]['width'] . '" height="' . $data['pictures']['sizes'][ $this->vimeoThumbnailSize ]['height'] . '" alt="' . $data['name'] . '" />';
        $html .= '<div class="item-overlay"><span class="title">' . $data['name'] . '</span></div>';
        $html .= '</a>';
        $html .= '</div>';

        return $html;
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
    public function addFilter( $elementNames, $defaultStartFilter, $addAllFilter ) {
        // All filter is not allowed to have . infront of *
        if ( $defaultStartFilter == '' || $defaultStartFilter == '*' ) {
            $html = '<div id="vimeo-filters" class="button-group" data-default-start-filter="*">';
        } else {
            $html = '<div id="vimeo-filters" class="button-group" data-default-start-filter="' . '.' . $defaultStartFilter . '">';
        }
        // check if "all" filter should be displayed
        if ( $addAllFilter === true ) {
            $html .= '<button data-filter="*">All</button>';
        }

        if ( count( $elementNames ) > 0 ) {
            foreach ( $elementNames as $filter ) {
                $html .= '<button data-value="' . $this->umlaute->convertAccentAndBlankspace( $filter['name'] ) . '">' . $filter['name'] . '</button>';
            }
        }

        $html .= '</div>';

        return $html;
    }

}
