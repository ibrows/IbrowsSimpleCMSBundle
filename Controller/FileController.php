<?php

namespace Ibrows\SimpleCMSBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * FileController
 *
 * @Route("/file")
 */
class FileController extends Controller
{
    
    public function config(){
        
        //Access Control Setting
        /**
         * turn off => false
         * by session => true
         */
        $conf['CONFIG_ACCESS_CONTROL_MODE'] =  false;
        $conf['CONFIG_LOGIN_USERNAME'] =  'ajax';
        $conf['CONFIG_LOGIN_PASSWORD'] =  '123456';
        $conf['CONFIG_LOGIN_PAGE'] =  'ajax_login.php'; //the url to the login page
        
        //SYSTEM MODE CONFIG
        /**
         * turn it on when you have this system for demo purpose
         *  that means changes made to each image is not physically applied to it
         *  and all uploaded files/created folders will be removed automatically
         */
        $conf['CONFIG_SYS_DEMO_ENABLE'] =  false;
        $conf['CONFIG_SYS_VIEW_ONLY'] =  false; //diabled the system, view only
        $conf['CONFIG_SYS_THUMBNAIL_VIEW_ENABLE'] =  true;//REMOVE THE thumbnail view if false
        
        //User Permissions
        $conf['CONFIG_OPTIONS_DELETE'] =  true; //disable to delete folder
        $conf['CONFIG_OPTIONS_CUT'] =  true;	//disalbe to cut a file/folder
        $conf['CONFIG_OPTIONS_COPY'] =  true;	//disable to copy a file/folder
        $conf['CONFIG_OPTIONS_NEWFOLDER'] =  true; //disable to create new folder
        $conf['CONFIG_OPTIONS_RENAME'] =  true; //disable to rename the file/folder
        $conf['CONFIG_OPTIONS_UPLOAD'] =  true; //disable to upload the file
        $conf['CONFIG_OPTIONS_EDITABLE'] =  true; //disable image editor and text editor
        $conf['CONFIG_OPTIONS_SEARCH'] =  true; //disable to search documents
        //FILESYSTEM CONFIG
        /*
         * CONFIG_SYS_DEFAULT_PATH is the default folder where the files would be uploaded to
        and it must be a folder under the CONFIG_SYS_ROOT_PATH or the same folder
        these two paths accept relative path only, don't use absolute path
        */

        $conf['CONFIG_SYS_DEFAULT_PATH'] = $this->container->getParameter('ibrows_simple_cms.upload_dir'); // $this->container->getParameter('kernel.root_dir'). '/../web/'.'uploads/documents/'; //accept relative path only
        $conf['CONFIG_SYS_ROOT_PATH'] =  $conf['CONFIG_SYS_DEFAULT_PATH'];	//accept relative path only
        $conf['CONFIG_SYS_FOLDER_SHOWN_ON_TOP'] =  true; //show your folders on the top of list if true or order by name
        $conf['CONFIG_SYS_DIR_SESSION_PATH'] =  'session/';
        $conf['CONFIG_SYS_PATTERN_FORMAT'] =  'list'; //three options: reg ,csv, list, this option define the parttern format for the following patterns
        /**
         * reg => regulare expression
         * csv => a list of comma separated file/folder name, (exactly match the specified file/folders)
         * list => a list of comma spearated vague file/folder name (partially match the specified file/folders)
         *
         */
        //more details about regular expression please visit http://nz.php.net/manual/en/function.eregi.php
        $conf['CONFIG_SYS_INC_DIR_PATTERN'] =  ''; //force listing of folders with such pattern(s. separated by , if multiple
        $conf['CONFIG_SYS_EXC_DIR_PATTERN'] =  ''; //will prevent listing of folders with such pattern(s. separated by , if multiple
        $conf['CONFIG_SYS_INC_FILE_PATTERN'] =  ''; //force listing of fiels with such pattern(s. separated by , if multiple
        $conf['CONFIG_SYS_EXC_FILE_PATTERN'] =  ''; //will prevent listing of files with such pattern(s. separated by , if multiple
        $conf['CONFIG_SYS_DELETE_RECURSIVE'] =  1; //delete all contents within a specific folder if set to be 1
        
        //UPLOAD OPTIONS CONFIG
        $conf['CONFIG_UPLOAD_MAXSIZE'] =  50 * 1024 & 1024 ; //by bytes
        //$conf['CONFIG_UPLOAD_MAXSIZE'] =  2048; //by bytes
        //$conf['CONFIG_UPLOAD_VALID_EXTS'] =  'txt';//
        
        $conf['CONFIG_EDITABLE_VALID_EXTS'] =  'txt,htm,html,xml,js,css'; //make you include all these extension in CONFIG_UPLOAD_VALID_EXTS if you want all valid
        
        $conf['CONFIG_OVERWRITTEN'] =  false; //overwirte when processing paste
        $conf['CONFIG_UPLOAD_VALID_EXTS'] =  'gif,jpg,png,txt'; //
        //$conf['CONFIG_UPLOAD_VALID_EXTS'] =  'gif,jpg,png,bmp,tif,zip,sit,rar,gz,tar,htm,html,mov,mpg,avi,asf,mpeg,wmv,aif,aiff,wav,mp3,swf,ppt,rtf,doc,pdf,xls,txt,xml,xsl,dtd';//
        $conf['CONFIG_VIEWABLE_VALID_EXTS'] =  'gif,bmp,txt,jpg,png,tif,html,htm,js,css,xml,xsl,dtd,mp3,wav,wmv,wma,rm,rmvb,mov,swf';
        //$conf['CONFIG_UPLOAD_VALID_EXTS'] =  'gif,jpg,png,txt'; //
        $conf['CONFIG_UPLOAD_INVALID_EXTS'] =  '';
        
        //Preview
        $conf['CONFIG_IMG_THUMBNAIL_MAX_X'] =  100;
        $conf['CONFIG_IMG_THUMBNAIL_MAX_Y'] =  100;
        $conf['CONFIG_THICKBOX_MAX_WIDTH'] =  700;
        $conf['CONFIG_THICKBOX_MAX_HEIGHT'] =  430;

        
        $conf['CONFIG_WEBSITE_DOCUMENT_ROOT'] =  '';
        //theme related setting
        /*
         *	options avaialbe for CONFIG_EDITOR_NAME are:
        stand_alone
        tinymce
        fckeditor
        */
        //CONFIG_EDITOR_NAME replaced CONFIG_THEME_MODE since @version 0.8
        $conf['CONFIG_EDITOR_NAME'] =  (!empty($_GET['editor'])?secureFileName($_GET['editor']):'tinymce');
        $conf['CONFIG_THEME_NAME'] =  ( !empty($_GET['theme'])?secureFileName($_GET['theme']):'default');  //change the theme to your custom theme rather than default
        $conf['CONFIG_DEFAULT_VIEW'] =  ($conf['CONFIG_SYS_THUMBNAIL_VIEW_ENABLE'] ?'detail':'detail'); //thumnail or detail
        $conf['CONFIG_DEFAULT_PAGINATION_LIMIT'] =  10;
        $conf['CONFIG_LOAD_DOC_LATTER'] =  false; //all documents will be loaded up after the template has been loaded to the client
        
        //General Option Declarations
        //LANGAUGAE DECLARATIONNS
        
        $conf['CONFIG_LANG_INDEX'] =  'language'; //the index in the session
        $conf['CONFIG_LANG_DEFAULT'] =  (!empty($_GET['language']) && file_exists(DIR_LANG . secureFileName($_GET['language']) . '.php')?secureFileName($_GET['language']):'en'); //change it to be your language file base name, such en

        
      
        //URL Declartions
        $conf['CONFIG_URL_IMAGE_PREVIEW'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_image_preview'));
        $conf['CONFIG_URL_CREATE_FOLDER'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_create_folder.php'));
        $conf['CONFIG_URL_DELETE'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_delete_file.php'));
        $conf['CONFIG_URL_HOME'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajaxfilemanager.php'));
        $conf['CONFIG_URL_UPLOAD'] = $this->generateUrl("ibrows_simple_cms_file_manager",  array('incl'=>'ajax_file_upload.php'));
        $conf['CONFIG_URL_PREVIEW'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_preview.php'));
        $conf['CONFIG_URL_SAVE_NAME'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_save_name.php'));
        $conf['CONFIG_URL_IMAGE_EDITOR'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_image_editor.php'));
        $conf['CONFIG_URL_IMAGE_SAVE'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_image_save.php'));
        $conf['CONFIG_URL_IMAGE_RESET'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_editor_reset.php'));
        $conf['CONFIG_URL_IMAGE_UNDO'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_image_undo.php'));
        $conf['CONFIG_URL_CUT'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_file_cut.php'));
        $conf['CONFIG_URL_COPY'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'ajax_file_copy.php'));
        $conf['CONFIG_URL_LOAD_FOLDERS'] =  $this->generateUrl("ibrows_simple_cms_file_manager", array('incl'=>'_ajax_load_folders.php'));
        
        $conf['CONFIG_URL_DOWNLOAD'] =  'ajax_download.php';
        $conf['CONFIG_URL_TEXT_EDITOR'] =  'ajax_text_editor.php';
        $conf['CONFIG_URL_GET_FOLDER_LIST'] =  'ajax_get_folder_listing.php';
        $conf['CONFIG_URL_SAVE_TEXT'] =  'ajax_save_text.php';
        $conf['CONFIG_URL_LIST_LISTING'] =  'ajax_get_file_listing.php';
        $conf['CONFIG_URL_IMG_THUMBNAIL'] =  'ajax_image_thumbnail.php';
        $conf['CONFIG_URL_FILEnIMAGE_MANAGER'] =  'ajaxfilemanager.php';
        $conf['CONFIG_URL_FILE_PASTE'] =  'ajax_file_paste.php';  


        return $conf;
    }
    
    
    
    /**
     * Lists all Content entities.
     *
     * @Route("/manager", name="ibrows_simple_cms_file_manager")
     * @Template()
     */    
    function managerAction(){
        $conf = $this->config();
        //require_once $inlcdir.'ajaxfilemanager.php';

        return $this->render('IbrowsSimpleCMSBundle:File:ajaxfilemanager.html.php', $conf);
        

    }
    
    /**
     * Lists all Content entities.
     *
     * @Route("/{type}" ,defaults={"type" = "ajaxfilemanager"}, name="ibrows_simple_cms_file_all")
     * @Template()
     */    
    function allAction($type){
        $conf = $this->config();
        
        return $this->render('IbrowsSimpleCMSBundle:File:'.$type.'', $conf);
    }
    
    
}