<?php

namespace App\Jobs;

use App\Models\Train;
use Curl\Curl;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TrainPython implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Python 订票
     * @throws \ErrorException
     */
    public function handle()
    {
        $url = 'http://127.0.0.1:5000/order';
        $train = Train::find($this->id);
        $train->python_type = 1;
        $train->save();

        $data = [
            "username"  => $train->username,
            "password"  => $train->pwd,
            "date"      => $train->train_date,
            "start"     => $train->start_station,
            "end"       => $train->to_station,
            "code"      => $train->train_no,
        ];

        $curl = new Curl();
        $curl->setHeader('Content-Type','application/x-www-form-urlencoded');
        $curl->post($url,$data);
        $curl->close();
    }
}