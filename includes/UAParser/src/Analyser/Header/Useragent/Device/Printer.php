<?php

namespace WhichBrowser\Analyser\Header\Useragent\Device;

use WhichBrowser\Constants;

trait Printer
{
    private function detectPrinter($ua)
    {
        if (!preg_match('/(TASKalfa|CanonIJCL|PrintSmart)/ui', $ua)) {
            return;
        }

        /* TASKalfa */

        if (preg_match('/TASKalfa ([0-9A-Z]+)/iu', $ua, $match)) {
            $this->data->device->setIdentification([
                'manufacturer'  =>  'Kyocera',
                'model'         =>  'TASKalfa ' . $match[1],
                'type'          =>  Constants\DeviceType::PRINTER
            ]);
        }


        /* Canon IJ */

        if (preg_match('/CanonIJCL/iu', $ua, $match)) {
            $this->data->device->setIdentification([
                'manufacturer'  =>  'Canon',
                'model'         =>  'IJ Printer',
                'type'          =>  Constants\DeviceType::PRINTER
            ]);
        }

        /* HP Web PrintSmart */

        if (preg_match('/HP Web PrintSmart/iu', $ua, $match)) {
            $this->data->device->setIdentification([
                'manufacturer'  =>  'HP',
                'model'         =>  'Web PrintSmart',
                'type'          =>  Constants\DeviceType::PRINTER
            ]);
        }
    }
}
