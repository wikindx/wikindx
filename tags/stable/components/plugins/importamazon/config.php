<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class importamazon_CONFIG
{
    /** array */
    public $menus = ['res'];
    /** int */
    public $authorize = 1;
    /** float */
    public $wikindxVersion = 7;
    
    /**
     * NB: you have to get an ACCESS KEY that MATCHES the END POINT's REGION you want
     * $accessKey is the Amazon Web Services access key
     */
    public $accessKey = "";

    /**
     * $secretAccessKey is the Amazon Web Services secret access key
     */
    public $secretAccessKey = "";

    /**
     * An AssociateTag is an alphanumeric token distributed by Amazon that uniquely identifies an Associate.
     */
    public $associateTag = "";

    /**
     * You can find the list of Product Advertising API Endpoints available at :
     * https://docs.aws.amazon.com/AWSECommerceService/latest/DG/AnatomyOfaRESTRequest.html#EndpointsandWebServices
     * If possible, We particularly advise you to choose a secure endpoint (https).
     */
    public $productAdvertisingAPIEndpoint = ""; // Don't terminate with / or ?
}
