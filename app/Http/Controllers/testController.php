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
        /*XLM,*/                    182=>'XLM',
        /*TRX,*/                    185=>'TRX',
        /*Chainlink (LINK), */      197=>'LINK',
                                    172=>'BCH',
                                    137=>'BSV',
                                    149=>'XMR',
                                    140=>'DASH',
                                    162=>'ZEC',
                                    163=>'OMNI(USDT)',
                                    36=>'ERC20(USDT)',
                                    23=>'USDC',
                                    24=>'TUSD',
                                    189=>'PAX',
                                    177=>'NEO',
                                    178=>'EOS',
                                    133=>'WAVES',
                                    175=>'XTZ',
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
    /*Наличные USD */                   89=>'USD',
/*    Paypal USD,*/                     45=>'USD',
/*Paypal EUR, */                        80=>'EUR',
/*Paypal RUB, */                        98=>'RUB',
/*Payeer USD, */                        108=>'USD',
/*Payeer EUR, */                        122=>'EUR',
/*Payeer RUB,*/                         117=>'RUB',
/*ВТБ, */                               51=>'RUB',
/*Revolut USD, */                       192=>'USD',
/*Revolut EUR */                        193=>'EUR',

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
        $set_fiat=\Cache::get('fiat');
        $set_crypto=\Cache::get('crypto');
        //selected fiat from settings
        $res_fiat=Arr::where($res, function ($value, $key) use ($fiat,$set_fiat) {
            return (isset($fiat[$value->curr1]) and isset($set_fiat[$value->curr1]));
        });

        //amount in rub > then in settings
        if (\Cache::get('checkbox_min_rub')) {
            $min=\Cache::get('checkbox_min_rub_sum');
            $res_fiat = Arr::where($res_fiat, function ($value, $key)use ($min) {
                return $value->rub_rate >= $min;
            });
        }

        foreach ($res_fiat as $v){
            $n=[];
            $n['fiat1']=$v->Name1;
            $n['crypto1']=$v->Name2;
            $n['rate1']=$v->rate;
            $n['rate_rub1']=$v->rub_rate;
            //selected crypto from settings
            $crypto_flt =Arr::where($res, function ($value, $key) use ($v,$set_crypto,$set_fiat) {
                return ($v->curr2 == $value->curr1) and ($v->curr1!= $value->curr2) and isset($set_crypto[$v->curr2]) and isset($set_fiat[$value->curr2]);
            });
            foreach ($crypto_flt as $c2){
                $n2=$n;
                $n2['crypto2']=$c2->Name1;
                $n2['fiat2']=$c2->Name2;
                $n2['currs']=$currs[$fiat[$c2->curr2]];
                $n2['rate_rub2']=(1 / ($c2->rate) ) * ($currs[$fiat[$c2->curr2]]);
                $n2['rate2']=1/$c2->rate;
                $n2['diff']=(!$n2['rate_rub2'])?0:(1-$n['rate_rub1']/$n2['rate_rub2'])*100;
                $table[]=$n2;
            }
        }

        //отрицательные значения
        if (!\Cache::get('checkbox_minus'))
            $table =Arr::where($table, function ($value, $key)  {
                return $value['diff']>0;
            });

        $table = Arr::sort($table,function ($value) {
              return $value['diff'];
          });
        $table=array_reverse($table);
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
        return view('home', [
            'res' => $table,
            'usd'=>\Cache::get('USD'),
            'eur'=>\Cache::get('EUR'),
            'dt'=>\Cache::get('dt'),
        ]);
    }

    function updateDB(){
        $temp_filename = storage_path('app').'\\be.zip';
        $zipFilePath=storage_path('app').'\\be.zip';
        $this->downloadbestExchange();
        $this->saveBestExchangetoDatabase($zipFilePath);
        if (!isset($_GET['silent']))
            return redirect('/');
        else
            exit(0);
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
        \Cache::put('dt',time());
        //дата обновления
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
                    $v[1]=($v[1]>0)?$v[1]:85;
                    \Cache::put('EUR', $v[1]);
                    $res['EUR'] = $v[1];
                }
                if ($v[0]=='USD000000TOD') {
                    $v[1]=($v[1]>0)?$v[1]:75;
                    \Cache::put('USD',$v[1]);
                    $res['USD'] = $v[1];
                }
            }
        else{
            $res['EUR'] = 85;
            $res['USD'] = 75;
        }
        return $res;
    }
   public function getFiat(){
        return $this->fiat;
    }
    public function getCrypto(){
            return $this->crypto;
    }
}
