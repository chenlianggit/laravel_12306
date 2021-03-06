<?php
/**
 * 订票-请求Python
 * User: chenliang
 * Date: 2018/12/26
 * Time: 下午11:43
 */

namespace App\Console\Commands\Train;

use App\Models\Train;
use Curl\Curl;
use Illuminate\Console\Command;
class Create extends Command
{
    protected $signature = "train:create-python {--id=}";

    protected $description = "订票-请求Python";

    /**
     *
     * Artisan::call('train:create-python',[ '--id' => $id ])
     * @throws \ErrorException
     */
    public function handle()
    {
        $id = (int)$this->option('id');

        $url = 'http://127.0.0.1:5000/order';
        $train = Train::find($id);
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