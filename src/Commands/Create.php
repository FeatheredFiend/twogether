<?php

namespace Martyn\Twogether\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Create extends Command
{
    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'create';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Cakeday Command Line Tool';


    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $file = "C:\\twogether\birthdays.txt"; // Read Birthdays File Stored in Root of Program
        $myFile = new \SplFileObject($file);
        $cakedaysArray = array(array("Date" => "Date", "SmallCakes" => "Number of Small Cakes", "LargeCakes" => "Number of Large Cakes", "Names" => "Names of People Getting Cake")); // Set Size of Array - Will be Deleted when Ordered
        $x = 0;
        while (!$myFile->eof()) {
            $explodedBirthday = explode(",", $myFile->fgets());
            $name = $explodedBirthday[0]; // Get Name
            $thisBirthday = $this->updateDate($explodedBirthday[1]); // get Birthday for Year
            $thisDayOff = $this->updateDate($explodedBirthday[1]); // Get Date OFf
            $day = $thisBirthday->format("D"); // Get Day of Birthday
            $monthDay = $thisBirthday->format("m-d"); // Get Month-Day of Birthday



            // Calculate for Christmas Day, Boxing Day, New Years Day and Saturdays and Sundays
            if ($monthDay == "12-25") {
                $thisDayOff->add(new \DateInterval('P2D'));
            } elseif ($monthDay == "12-26") {
                $thisDayOff->add(new \DateInterval('P1D'));
            } elseif ($monthDay == "01-01") {
                $thisDayOff->add(new \DateInterval('P1D'));
            } else if ($day == "Sat") {
                $thisDayOff->add(new \DateInterval('P2D'));
            } elseif ($day == "Sun") {
                $thisDayOff->add(new \DateInterval('P1D'));
            }

            $thisCakeDay = clone $thisDayOff;

            $dayCakeDay = $thisCakeDay->format("D");

            // Calculate Cake Day based on Day After Day Off
            if ($dayCakeDay == "Fri") {
                $thisDayOff->add(new \DateInterval('P3D'));
            } else if ($dayCakeDay == "Sat") {
                $thisCakeDay->add(new \DateInterval('P2D'));
            } elseif ($dayCakeDay == "Sun") {
                $thisCakeDay->add(new \DateInterval('P1D'));
            } else {
                $thisCakeDay = $thisCakeDay->add(new \DateInterval('P1D'));
            }

            // Get Pointers for Previous and Next Day
            $lastCakeDay = clone $thisCakeDay;
            $nextCakeDay = clone $thisCakeDay;
            $lastCakeDay = $lastCakeDay->sub(new \DateInterval('P1D'));
            $nextCakeDay = $nextCakeDay->add(new \DateInterval('P1D'));

            // Check if they exist with the Array
            $key = $this->searchForId($thisCakeDay->format("Y-m-d"),$cakedaysArray);
            $lastKey = $this->searchForId($lastCakeDay->format("Y-m-d"),$cakedaysArray);     
            $nextKey = $this->searchForId($nextCakeDay->format("Y-m-d"),$cakedaysArray);

                if (in_array($thisCakeDay->format("Y-m-d"), $cakedaysArray[$key])) {
                    // if Cakeday Exists in Array then Convert Small Cake to Large Cake and Append Name
                    $cakedaysArray[$key]["SmallCakes"] = 0;
                    $cakedaysArray[$key]["LargeCakes"] = 1;
                    $cakedaysArray[$key]["Name"] = $cakedaysArray[$key]["Name"] . ", " . $name;
                } else if (in_array($nextCakeDay->format("Y-m-d")  ,$cakedaysArray[$nextKey])) {
                    // if next Cake Day is assigned, then append to that Cake Day
                        $cakedaysArray[$nextKey]["SmallCakes"] = 0;
                        $cakedaysArray[$nextKey]["LargeCakes"] = 1;
                        $cakedaysArray[$nextKey]["Name"] = $cakedaysArray[$nextKey]["Name"] . ", " . $name;   
                } else {
                    if (($lastKey !== 0) && ($lastKey < $key)) {
                        // If Last Cake Day Exists then Add 1 Day to Create Next Cake Day
                        $thisCakeDay = $thisCakeDay->add(new \DateInterval('P1D'));
                        $cakedaysArray[$lastKey]["Date"] = $thisCakeDay->format("Y-m-d");
                        $cakedaysArray[$lastKey]["SmallCakes"] = 0;
                        $cakedaysArray[$lastKey]["LargeCakes"] = 1;
                        $cakedaysArray[$lastKey]["Name"] = $cakedaysArray[$lastKey]["Name"] . ", " . $name;
                    } else if (($lastKey !== 0) && ($lastKey > $key)) {
                        // If Last Cake Day Exists then Add 1 Day to Create Next Cake Day
                        $thisCakeDay = $thisCakeDay->add(new \DateInterval('P1D'));
                        $cakedaysArray[$lastKey]["Date"] = $thisCakeDay->format("Y-m-d");
                        $cakedaysArray[$lastKey]["SmallCakes"] = 0;
                        $cakedaysArray[$lastKey]["LargeCakes"] = 1;
                        $cakedaysArray[$lastKey]["Name"] = $cakedaysArray[$lastKey]["Name"] . ", " . $name;
                    } else {
                        // Add New Cakeday to Array
                        $cakedaysArray[] = array("Date" => $thisCakeDay->format("Y-m-d"),"SmallCakes" => 1, "LargeCakes" => 0, "Name" => $name);
                    }
                }
            $x++;

        }
        // Sort Array
        usort($cakedaysArray, function($a, $b) {
            return $a['Date'] <=> $b['Date'];
        });

        //Remove Title Names and then Replace then at the Start
        array_pop($cakedaysArray);
        array_unshift($cakedaysArray,array("Date" => "Date", "SmallCakes" => "Number of Small Cakes", "LargeCakes" => "Number of Large Cakes", "Names" => "Names of People Getting Cake"));
        
        // Create CSV and save at Root of Program
        $fp = fopen('Cakedays.csv', 'w');
        foreach ($cakedaysArray as $fields) {
            fputcsv($fp, $fields);
        }
        
        fclose($fp);
        echo "The Cake is a Lie" . PHP_EOL;


        return Command::SUCCESS;
    }

    /**
     * @param String $dateString
     * @return DateTime
     */
    public function updateDate($dateString){
        $suppliedDate = new \DateTime($dateString);
        $currentYear = (int)(new \DateTime())->format('Y');
        return (new \DateTime())->setDate($currentYear, (int)$suppliedDate->format('m'), (int)$suppliedDate->format('d'));
    }

    public function searchForId($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['Date'] === $id) {
                return $key;
            }
        }
        return 0;
     }

}

