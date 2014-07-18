<?php

namespace Ibrows\SimpleCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ibrows\SimpleCMSBundle\Entity\TextContent
 * 
 * @ORM\Table(name="scms_textcontent")
 * @ORM\Entity(repositoryClass="Ibrows\SimpleCMSBundle\Repository\TextContentRepository")
 */
 class TextContent extends Content
{
    
    /**
     * @var string $text
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    //return html
    public function toHTML(\Ibrows\SimpleCMSBundle\Helper\HtmlFilter $filter,array $args){
        $return ='';
        $arr = parent::mergeUserArgs($args, array('attr'=>array('class'=>'simplecms-textcontent')));
        foreach($arr['attr'] as $key => $val){
            $return .= "$key=\"$val\"";
        } 
        $text = $this->getText();
        if(!isset ($args['html'])  ||  $args['html'] != true){
            $text = $filter->filterHtml($text);
            $text = nl2br($text); 
        }else if(!isset ($args['nojs'])  ||  $args['nojs'] != true){
            $text = self::mailSpamProtect($text);
        }                   
        
        return '<span '.$return.'>'
                .$text.
            '</span>'
            ;
    }
    
    public static function mailSpamProtect($html){
        $matches = array();
        preg_match_all('!<a([^>])+mailto:([^\s<>"@]+)@([^\s<>"@]+)([^>]*)>([^<]+)</a>!', $html, $matches);

        foreach($matches[0] as $i => $link) {
            $hash = sha1($link);
            $replace = <<<EOF
<span id="id_{$hash}"></span>
<script type="text/javascript">
<!--
(function(){
var string1 = '{$matches[2][$i]}';
var string2 = '@';
var string3 = '{$matches[3][$i]}';
document.getElementById('id_{$hash}').innerHTML = '<a href=' + 'mail' + 'to:' + string1 + string2 + string3 + '>' + string1 + string2 + string3 + '</a>';
})();
-->
</script>
EOF;
            $html = str_replace($link, $replace, $html);

        }

        return $html;
    }
}