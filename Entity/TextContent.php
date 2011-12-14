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
            $replacment = '
<script language="JavaScript" type="text/javascript">
<!--
	var string1 = "\\2";
	var string2 = "@";
	var string3 = "\\3";
	var string4 = string1 + string2 + string3;
	document.write("<a href=" + "mail" + "to:" + string1 +
		string2 + string3 + ">" + string4 + "</a>");
//-->
</script>
';
            return preg_replace('!<a([^>])+mailto:([^\s<>"@]+)@([^\s<>"@]+)([^>]*)>([^<]+)</a>!', $replacment, $html);        
    }
}