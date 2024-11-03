<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * @deprecated
 * @experimental
 */
class ScrapCardMarketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cookies = [
            "PHPSESSID" =>	"1dh3dbcbahavapficv2qhnsduo",
            "__cf_bm" =>	"KqEqHKWzaU9d4p2PRvtJsABlWYRt9AJ2I2eRSJGRs0Y-1727714158-1.0.1.1-HUIG.f.EKmXn.ZQs0OZvFyS.fqGQeu_PavQaSJSGjfdovnLmfeLfdlUn2rdEYqIMUkG.jZBRHPBem0NorKknWw",
            "_cfuvid" =>	"tTXYxHTCIdjngp8Okt0ALgSqq9NUqqqDTuHFEANRPfE-1727623867206-0.0.1.1-604800000",
            "_vis_opt_s" =>	"4%7C",
            "_vis_opt_test_cookie" =>	"1",
            "_vwo_ds" =>	"3%3At_0%2Ca_0%3A0%241725569499%3A67.06027622%3A%3A62_0%2C61_0%2C60_0%2C59_0%2C58_0%2C57_0%2C49_0%2C48_0%2C45_0%3A240_0%2C228_0%2C167_0%2C163_0%2C158_0%2C44_0%2C33_0%2C16_0%2C1_0%3A0",
            "_vwo_sn" =>	"2144658%3A4",
            "_vwo_ssm" =>	"1",
            "_vwo_uuid" =>	"D48380F220FF0AC1BAAFEB64527BE15D7",
            "_vwo_uuid_v2" =>	"D48380F220FF0AC1BAAFEB64527BE15D7|faac06d005e20498ebc4ae7a58216430",
            "cf_clearance" =>	"QMebr1SCsD5Z3rsBCiKr0d1esSQShgsDHPG77kPNa5k-1726006776-1.2.1.1-oG_4o4k31aXorgxJHiJfeRpsz9koFKQfIs3UJHp17gUP5pVakVu00qpnYCnSEk88_emn.16W0H5lq3wCgzZSEueU9CnguyuA.u0v53T7k1J8hm3kPUtIsshx80u5rG10K97YC7d1_cCFP_vGkD98yfXu0wjGWQfbkWurVkHckah6dksKvJKrnHwHaDbRvi04B08zWIZP.owtlAuhjNBewnGYztQ9lGR8dJGpJfJar5hepE2IAVZ.7YG.G7ImND349oRaLqqwC0_5sDgWYiBhEsXrJj.blt_khVxBu9JsoDRxkQXwEXqx7aw_1NpTZfLpVb6nijVKbxUP1h5TTQdv4WZUdD7sCQnXpTT2kc2gG6g7VV00FzrYHv3o.Y2h5Fg4n3hWJ.sOZLCn7QXWVHFASVEb5C_qWRQ42I5GoxTqSAX2jyxlFlxbnjNMyDi00dN4",
            "cookie_settings" =>	"preferences%3D1%2Cstatistics%3D1%2Cmarketing%3D1",
            "data" =>	"6b8c438e67a3fb871533b7bce2442678",
            "idUser" =>	"992300579"
        ];

        $headers = [
            'authority' => 'www.northerntool.com',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
            'cache-control' => 'max-age=0',
            'if-modified-since' => 'Sat, 11 Mar 2023 04:07:21 GMT',
            'if-none-match' => 'W/"3503-5f69803b20b49"',
            'sec-ch-ua' => '"Chromium";v="110", "Not A(Brand";v="24", "Google Chrome";v="110"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'same-origin',
            'sec-fetch-user' => '?1',
            'service-worker-navigation-preload' => 'true',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
        ];

        $response = Http::withHeaders($headers)
            ->withCookies($cookies, 'https://www.cardmarket.com')
            ->get('https://www.cardmarket.com/en/YuGiOh/Products/Singles/Force-of-the-Breaker/A-Cell-Breeding-Device')
            ->body();

        dd($response);
    }
}
