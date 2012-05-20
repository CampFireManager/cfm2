<?php

interface Interface_Object
{
    public static function brokerByID($intID);
    public static function brokerByColumnSearch($column, $value);
    public static function countByColumnSearch($column, $value);
    public static function lastChangeByColumnSearch($column, $value);
    public static function brokerAll();
    public static function lastChangeAll();
    public static function countAll();
}