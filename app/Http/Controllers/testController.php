<?php

namespace App\Http\Controllers;

//
use App\BestchangeRates;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use ZipArchive;
use App\BestchangeCurrs;
use DB;
class testController extends Controller
{

private $crypto =[
        /* BTC, */                  93=>'BTC',
         /*ETH,*/                   139=>'ETH',
        /*LTC,*/                    99=>'LTC',
         /*XRP, */                  161=>'XRP',
        /*XMR,*/                    149=>'XMR',
        /*Tether ERC-20 (USDT), */  36=>'USDT',
        /*XLM,*/                    182=>'XLM',
        /*TRX,*/                    185=>'TRX',
        /*Chainlink (LINK), */      197=>'LINK'
        ];

private $fiat = [
    /* Advanced Cash USD, */            88=>'USD',
    /*Advanced Cash EUR,*/              120=>'EUR',
    /*Advanced Cash RUB, */             121=>'RUB',
    /*Capitalist USD,  */               145=>'USD',
    /*Capitalist RUB,*/                 85=>'RUB',
    /*Криптобиржи USD,*/                148=>'USD',
    /*Криптобиржи EUR,*/                153=>'EUR',
    /*Сбербанк (RUB),*/                 42=>'RUB',
    /*Альфа-банк (RUB),*/               52=>'RUB',
    /*Альфа cash-in RUB,*/              62=>'RUB',
    /*Тинькофф (RUB), */                105=>'RUB',
    /*ТКС cash-in (RUB),*/              46=>'RUB',
    /*Visa/Mastercard RUB, */           59=>'RUB',
    /*Visa/Mastercard USD,*/            58=>'USD',
    /*Visa/Mastercard EUR,*/            65=>'EUR',
    /*Любой банк USD,*/                 69=>'USD',
    /*Любой банк EUR,*/                 70=>'EUR',
    /*Любой банк RUB,*/                 71=>'RUB',
    /*Sepa EUR, */                      171=>'EUR',
    /*Наличные RUB,*/                   91=>'RUB',
    /*Наличные EUR,*/                   141=>'EUR',
    /*Наличные USD */                   89=>'USD'
];

    function prepareTable($res){
        $table=[];
        $currs=[
            'USD'=>\Cache::get('USD'),
            'EUR'=>\Cache::get('EUR'),
            'RUB'=>1
        ];
        $fiat=$this->fiat;
        $crypto=$this->crypto;
        $res_fiat=Arr::where($res, function ($value, $key) use ($fiat) {
            return (isset($fiat[$value->curr1]));
        });

        $res_crypto=Arr::where($res, function ($value, $key) use ($crypto) {
            return (isset($crypto[$value->curr1]));
        });

        foreach ($res_fiat as $v){
            $n=[];
            $n['fiat1']=$v->Name1;
            $n['crypto1']=$v->Name2;
            $n['rate1']=$v->rate;
            $n['rate_rub1']=$v->rub_rate;
            $crypto_flt =Arr::where($res, function ($value, $key) use ($v) {
                return ($v->curr2 == $value->curr1) and ($v->curr1!= $value->curr2);
            });
            foreach ($crypto_flt as $c2){
                $n2=$n;
                $n2['crypto2']=$c2->Name1;
                $n2['fiat2']=$c2->Name2;
                $n2['currs']=$currs[$fiat[$c2->curr2]];
                $n2['rate_rub2']=(1 / ($c2->rate) ) * ($currs[$fiat[$c2->curr2]]);
             //   $n2['rate_rub2']=$c2->rub_rate ;
                $n2['rate2']=1/$c2->rate;
                $n2['diff']=(1-$n['rate_rub1']/$n2['rate_rub2'])*100;
                $table[]=$n2;
            }
        }
          $table = Arr::sort($table,function ($value) {
              return $value['diff'];
          });
        $table=array_reverse($table);
      //  dd($table);
        return $table;
    }

    function index(){
        $res = DB::table('bestchange_rates')
            ->leftJoin('bestchange_currs AS C1', 'C1.id', '=', 'bestchange_rates.curr1')
            ->leftJoin('bestchange_currs AS C2', 'C2.id', '=', 'bestchange_rates.curr2')
            ->select('C1.name as Name1', 'C2.name AS Name2', 'bestchange_rates.*')
            ->get();

        $res=$res->toArray();
        $table = $this->prepareTable($res);
        /*
        while ($res){
            $el=array_shift($res);
            $curr2=$el->curr2;
            $table[]=$el;
            $filtered = Arr::where($res, function ($value, $key) use ($curr2) {
                return ($value->curr1==$curr2);
            });
           foreach ($filtered as $k=>$v) {
               $table[]=$v;
               if (isset($res[$k]))
                   unset($res[$k]);
           }
        }
       */
        return view('home', [
            'res' => $table,
            'usd'=>\Cache::get('USD'),
            'eur'=>\Cache::get('EUR')
        ]);
    }

    function updateDB(){
        $temp_filename = storage_path('app').'\\be.zip';
        $zipFilePath=storage_path('app').'\\be.zip';
        $this->downloadbestExchange();
        $this->saveBestExchangetoDatabase($zipFilePath);
        return redirect('/');
    }


    function downloadbestExchange(){
        $temp_filename = storage_path('app').'\\be.zip';
         $fp = fopen($temp_filename, "w");
         fputs($fp, file_get_contents("http://api.bestchange.ru/info.zip"));
         fclose($fp);
    }

    function saveBestExchangetoDatabase($temp_filename){
        $zip = new ZipArchive;
        if (!$zip->open($temp_filename)) exit("error");
        $currencies = array();
        $tt=$zip->getFromName("bm_cy.dat");
        $tt=iconv('CP1251','UTF-8',$tt);
        //$tt= mb_convert_encoding($tt,  mb_detect_encoding($tt), 'KOI-8R');
        foreach (explode("\n",$tt ) as $value) {
            $entry = explode(";", $value);
            $currencies[$entry[0]] = $entry[2];
        }
        $exchangers = array();
        foreach (explode("\n", $zip->getFromName("bm_exch.dat")) as $value) {
            $entry = explode(";", $value);
            $exchangers[$entry[0]] = $entry[1];
        }
        $rates = array();
        foreach (explode("\n", $zip->getFromName("bm_rates.dat")) as $value) {
            $entry = explode(";", $value);
            $rates[$entry[0]][$entry[1]][$entry[2]] = array("rate"=>$entry[3] / $entry[4], "reserve"=>$entry[5], "reviews"=>str_replace(".", "/", $entry[6]));
        }
        $zip->close();
       //удаляем файл
        unlink($temp_filename);
        //заполняем названия
        BestchangeCurrs::query()->truncate();
        foreach ($currencies as $k=>$curr){
            BestchangeCurrs::create([
                'id' => $k,
                'name' => $curr
            ]);
        }
        //
        $fiat_rates= $this->getFiatRates();
        //массивы
        $fiat=$this->fiat;
        $crypto = $this->crypto;
        $from=[];
        $to=[];

        BestchangeRates::query()->truncate();


        foreach ($fiat as $fk=>$fc)
            foreach ($crypto as $ck=>$cc){
                $from_cy = $fk;
                $to_cy = $ck;
              try {
                  uasort($rates[$from_cy][$to_cy], function ($a, $b) {
                      if ($a["rate"] > $b["rate"]) return 1;
                      if ($a["rate"] < $b["rate"]) return -1;
                      return (0);
                  });
              }
              catch (\Exception $e){           continue;               }

                $entry = array_shift($rates[$from_cy][$to_cy]);
                if ($entry) {
                    $a=[
                        'curr1'=>$from_cy,
                        'curr2'=>$to_cy,
                        'rate'=>$entry["rate"]
                      //  'rate'=>($entry["rate"] < 1)?1/$entry["rate"]:$entry["rate"]
                    ];
                    $from[]=$a;
                }

                $from_cy = $ck;
                $to_cy = $fk;
              try {
                  uasort($rates[$from_cy][$to_cy], function ($a, $b) {
                      if ($a["rate"] > $b["rate"]) return 1;
                      if ($a["rate"] < $b["rate"]) return -1;
                      return (0);
                  });
              }
              catch (\Exception $e){           continue;               }

                $entry = array_shift($rates[$from_cy][$to_cy]);
                if ($entry) {
                    $a=[
                        'curr1'=>$from_cy,
                        'curr2'=>$to_cy,
                        'rate'=>$entry["rate"]
                      //  'rate'=>($entry["rate"] < 1)?1/$entry["rate"]:$entry["rate"]
                    ];
                    $to[]=$a;
                }

            }

             //calc the result
             $res=[];
            foreach ($from as  $k=>$v){
                $diff=0;
                $val = Arr::first($to, function ($value, $key) use ($v) {
                    return (($value['curr1'] == $v['curr2']) and ($value['curr2'] == $v['curr1'])) ;
                });
                $from[$k]['diff']=($val)?(1-$v['rate']/$val['rate'])*100:0;
            }

            $sorted = array_values(Arr::sort($from, function ($value) {
                return $value['diff'];
            }));
            $sorted=array_reverse($sorted);
            //save the result to database
            foreach ($sorted as $v){
                BestchangeRates::create([
                    'curr1'=>$v['curr1'],
                    'curr2'=>$v['curr2'],
                    'rate'=>$v['rate'],
                    'diff'=>0,
                 //   'diff'=>$v['diff'],
                    'rub_rate'=>$v['rate'] *
                        ((isset($fiat[$v['curr1']]))? $fiat_rates[$fiat[$v['curr1']]]:1) *
                        ((isset($fiat[$v['curr2']]))? $fiat_rates[$fiat[$v['curr2']]]:1)
                ]);
                $val = Arr::first($to, function ($value, $key) use ($v) {
                    return (($value['curr1'] == $v['curr2']) and ($value['curr2'] == $v['curr1'])) ;
                });
                if ($val){
                    BestchangeRates::create([
                        'curr1'=>$val['curr1'],
                        'curr2'=>$val['curr2'],
                        'rate'=>$val['rate'],
                        'diff'=>0,
                  //      'diff'=>$v['diff'],
                        'rub_rate'=>$val['rate'] *
                            ((isset($fiat[$val['curr1']]))? $fiat_rates[$fiat[$val['curr1']]]:1) *
                            ((isset($fiat[$val['curr2']]))? $fiat_rates[$fiat[$val['curr2']]]:1)
                    ]);
                }
            }
    }

    private function getFiatRates(){
        $req =json_decode(
            file_get_contents("http://iss.moex.com/iss/engines/currency/markets/selt/boards/CETS/securities.json?iss.only=marketdata&marketdata.columns=SECID,LAST"),
        1
        );
        $res=['RUB'=>1,'USD'=>75, 'EUR'=>85];
        if ($data=$req['marketdata']['data'])
            foreach ($data as $v){
                if (($v[0]=='EUR_RUB__TOD') and ($v[1]>0)) {
                    \Cache::put('EUR', $v[1]);
                    $res['EUR'] = $v[1];
                }
                if ($v[0]=='USD000000TOD') {
                    \Cache::put('USD',$v[1]);
                    $res['USD'] = $v[1];
                }
            }
        return $res;
    }

    private function ext($temp_filename){
        $zip = new ZipArchive;
        if (!$zip->open($temp_filename)) exit("error");
        $currencies = array();
        foreach (explode("\n", $zip->getFromName("bm_cy.dat")) as $value) {
            $entry = explode(";", $value);
            $currencies[$entry[0]] = $entry[2];
        }

        $exchangers = array();
        foreach (explode("\n", $zip->getFromName("bm_exch.dat")) as $value) {
            $entry = explode(";", $value);
            $exchangers[$entry[0]] = $entry[1];
        }
        $rates = array();
        foreach (explode("\n", $zip->getFromName("bm_rates.dat")) as $value) {
            $entry = explode(";", $value);
            $rates[$entry[0]][$entry[1]][$entry[2]] = array("rate"=>$entry[3] / $entry[4], "reserve"=>$entry[5], "reviews"=>str_replace(".", "/", $entry[6]));
        }
        $zip->close();
        //  unlink($temp_filename);

        $from_cy = 63;//QIWI
        $to_cy = 93;//Bitcoin

        echo("Курсы по направлению <a target=\"_blank\" href=\"https://www.bestchange.ru/index.php?from=" . $from_cy . "&to=" . $to_cy . "\">" . $currencies[$from_cy] . " &rarr; " . $currencies[$to_cy] . "</a>:<br>");
        uasort($rates[$from_cy][$to_cy], function ($a, $b) {
            if ($a["rate"] > $b["rate"]) return 1;
            if ($a["rate"] < $b["rate"]) return -1;
            return(0);
        });
        foreach ($rates[$from_cy][$to_cy] as $exch_id=>$entry) {
            echo("<a target=\"_blank\" href=\"https://www.bestchange.ru/click.php?id=" . $exch_id . "\">" . $exchangers[$exch_id] . "</a> &ndash;
отзывы <a target=\"_blank\" href=\"https://www.bestchange.ru/info.php?id=" . $exch_id . "\">" . $entry["reviews"] . "</a> &ndash;
курс <b>" . ($entry["rate"] < 1 ? 1 : $entry["rate"]) . "</b> " . $currencies[$from_cy] . " &rarr; <b>" . ($entry["rate"] < 1 ? 1 / $entry["rate"] : 1) . "</b> " . $currencies[$to_cy] . " &ndash;
резерв " . $entry["reserve"] . " " . $currencies[$to_cy] . "<br>");
        }

        echo("<br>Список валют:<br>");
        ksort($currencies);
        foreach ($currencies as $cy_id=>$cy_name) echo($cy_id . " &ndash; " . /*mb_convert_encoding($cy_name,"Windows-1252",'auto') */$cy_name. "<br>");

        echo("<br>Список обменников:<br>");
        ksort($exchangers);
        foreach ($exchangers as $exch_id=>$exch_name) echo($exch_id . " &ndash; " . $exch_name . "<br>");
    }

}
