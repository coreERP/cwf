<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\base;

/**
 * CwfType: This contains a list of constants representing many CwfTypes
 * 
 * @author girish
 */
class CwfType {
    //Summarises the access levels defined in Cwf
    public const AL_NO_ACCESS       = 0;
    public const AL_READONLY        = 1;
    public const AL_DATAENTRY       = 2;
    public const AL_AUTHORIZE       = 3;
    public const AL_CONSOLIDATED    = 4;
    
    const AL_ALLOW_DELETE  = TRUE;
    const AL_ALLOW_UNPOST = TRUE;
    
    // Summarises Business Object Types
    const BO_MASTER = 'Master';
    const BO_DOCUMENT = 'Document';
    const BO_REPORT = 'Report';
    
    // Summarises Filter Operands
    const FILTER_OP_EQUAL   = '=';
    const FILTER_OP_GREATER = '>=';
    const FILTER_OP_LESSER = '<=';
}
