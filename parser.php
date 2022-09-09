<?php

$headers = [];
$sourceFile = "";
$combinationFile = "";
$combinationFileWrite = "";
$isFileCsv = false;
$parsedData = [];
$argv = $_SERVER['argv'];

//Checking required arugments 
if($argv[1] != "--file" && (empty($argv[2]) OR $argv[2] != "--file"))
{
    echo "\nError: Source file not defined";
    exit;
}
else
{
    if($argv[1] == "--file")
    {
        $sourceFile = $argv[2];
        $combinationFile = !empty($argv[3]) ? explode("=", $argv[3])[1] : "";
    }
    else
    {
        $sourceFile = $argv[3];
        $combinationFile = !empty($argv[1]) ? explode("=", $argv[1])[1] : "";
    }
}

if(!file_exists($sourceFile))
{
    echo "\nError: Source file does not exist.";
    exit;
}


//Checking Source File type and opening for reading
$isFileCsv = strtolower((end((explode(".", $sourceFile))))) == "csv" ? true : false;
$file = fopen($sourceFile,"r") or die("\nError: Source file does not exist.");

//Extracting Headers
$headers = setHeaders(explode("," , clearData(fgets($file))));

//Opening combination file for writing
if($combinationFile != "")
{
    $combinationFileWrite = fopen($combinationFile,"w") or die("\nError: Failed to created a Combination file.");
}

//Reading File
while(!feof($file))
{
    if($isFileCsv)
    {
        $key = praseData(fgetcsv($file), $headers);
    }
    else
    {
        $key = praseData(explode("," ,clearData(fgets($file))), $headers);
    }

    if(isset($parsedData[$key]))
    {
        $parsedData[$key] += 1;
    }
    else
    {
        $parsedData[$key] = 1;
    }
}

if($combinationFile != "")
{
    writeCombinationFile($parsedData, $headers, $combinationFileWrite);
}


fclose($file);

//Function to parsse data
function praseData($data, $headers)
{
    if(empty($data[$headers['brand_name']]))
    {
        echo "Required field 'Make' is missing";
        return;
    }

    if(empty($data[$headers['model_name']]))
    {
        echo "Required field 'Model' is missing";
        return;
    }

    echo "\nMake: " . $data[$headers['brand_name']];
    echo "\nModel: " . $data[$headers['model_name']];
    echo "\nColour: " . $data[$headers['colour_name']];
    echo "\nCapacity: " . $data[$headers['gb_spec_name']];
    echo "\nNetwork: " . $data[$headers['network_name']];
    echo "\nGrade: " . $data[$headers['grade_name']];
    echo "\nCondition: " . $data[$headers['condition_name']];
    echo "\n";
    

    return $data[$headers['brand_name']] . "__" . $data[$headers['model_name']] . "__" . $data[$headers['colour_name']] . "__" . $data[$headers['gb_spec_name']] . "__" . $data[$headers['network_name']] . "__" . $data[$headers['grade_name']] . "__" . $data[$headers['condition_name']];
}

//Function for extracting headers
function setHeaders($data)
{
    $data = array_flip($data);
    return $data;
}

//Function to clear Data.
function clearData($data)
{
    $data = trim(str_replace('"', "", $data));
    return $data;
}

//Function to write combination file
function writeCombinationFile($data, $headers, &$combinationFileWrite)
{
    fwrite($combinationFileWrite, implode(",", array_flip($headers)) . ",count\n");

    foreach($data as $key => $count)
    {
        $temp = explode("__", $key);
        $temp[] = $count;

        fwrite($combinationFileWrite, implode(",", $temp) . "\n");
    }
}

?>