<?php
include_once 'salary_query.php';
$g_parser->ParseTable ('salary_tax_list', 
					   $Salary->getTax($m));