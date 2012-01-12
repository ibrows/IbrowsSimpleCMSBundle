<?php

namespace Ibrows\SimpleCMSBundle\Helper;

interface HtmlFilter {

    /**
     * @param string $stringToFilter
     * @return string filteredhtml 
     */
   public function filterHtml($stringToFilter);

    function generateUrl($name, $parameters = array(), $absolute = false);
}

?>
