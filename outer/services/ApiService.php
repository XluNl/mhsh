<?php


namespace outer\services;


use outer\utils\ExceptionAssert;
use outer\utils\StatusCode;

class ApiService
{

    public static function getDomainData($appId,$env,$type){

        $appDomainMap = self::getDomainMap();
        ExceptionAssert::assertTrue(key_exists($appId,$appDomainMap),StatusCode::createExpWithParams(StatusCode::APP_DOMAIN_MESSAGE_ERROR,'没有对应APPID信息'));
        $appDomain = $appDomainMap[$appId];
        $res = [];
        if ($env=='release'){
            $res = array_merge($res,$appDomain['env']['release']);
        }
        else {
            $res = array_merge($res,$appDomain['env']['test']);
        }
        return $res;
    }


    private static function getDomainMap(){
        return $appDomainMap = [
            //满好生活
            'wx1791fd2f9118b468'=>[
                'appId'=>'wx1791fd2f9118b468',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'customer-test.manhaoshenghuo.cn',
                        'imageDomain'=>'image-test.manhaoshenghuo.cn',
                        // 'businessDomain'=>'www.lfrontend.com',
                    ],
                    'release'=>[
                        'businessDomain'=>'customer.manhaoshenghuo.cn',
                        'imageDomain'=>'image.manhaoshenghuo.cn'
                    ]
                ]
            ],
            'wx4bcd15bb5112e22d'=>[
                'appId'=>'wx4bcd15bb5112e22d',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'delivery-test.manhaoshenghuo.cn',
                        'imageDomain'=>'image-test.manhaoshenghuo.cn',
                        // 'businessDomain'=>'www.lbusiness.com',
                    ],
                    'release'=>[
                        'businessDomain'=>'delivery.manhaoshenghuo.cn',
                        'imageDomain'=>'image.manhaoshenghuo.cn'
                    ]
                ]
            ],
            'wx8d37db829844ba3b'=>[
                'appId'=>'wx8d37db829844ba3b',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'alliance-test.manhaoshenghuo.cn',
                        'imageDomain'=>'image-test.manhaoshenghuo.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'alliance.manhaoshenghuo.cn',
                        'imageDomain'=>'image.manhaoshenghuo.cn'
                    ]
                ]
            ],



            //源味生活
            'wxb52abbc4fc2b3cb5'=>[
                'appId'=>'wxb52abbc4fc2b3cb5',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'customer.yuanweishenghuo.cn',
                        'imageDomain'=>'image.yuanweishenghuo.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'customer.yuanweishenghuo.cn',
                        'imageDomain'=>'image.yuanweishenghuo.cn'
                    ]
                ]
            ],
            'wx3822a16b495c2f13'=>[
                'appId'=>'wx3822a16b495c2f13',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'delivery.yuanweishenghuo.cn',
                        'imageDomain'=>'image.yuanweishenghuo.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'delivery.yuanweishenghuo.cn',
                        'imageDomain'=>'image.yuanweishenghuo.cn'
                    ]
                ]
            ],
            'wxf1361e2a9c0ebbe7'=>[
                'appId'=>'wxf1361e2a9c0ebbe7',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'alliance.yuanweishenghuo.cn',
                        'imageDomain'=>'image.yuanweishenghuo.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'alliance.yuanweishenghuo.cn',
                        'imageDomain'=>'image.yuanweishenghuo.cn'
                    ]
                ]
            ],



            //零里优选
            'wxf73f7222bdd8d3c1'=>[
                'appId'=>'wxf73f7222bdd8d3c1',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'customer.lingliyouxuan.cn',
                        'imageDomain'=>'image.lingliyouxuan.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'customer.lingliyouxuan.cn',
                        'imageDomain'=>'image.lingliyouxuan.cn'
                    ]
                ]
            ],
            'wxdccafdee72514552'=>[
                'appId'=>'wxdccafdee72514552',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'delivery.lingliyouxuan.cn',
                        'imageDomain'=>'image.lingliyouxuan.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'delivery.lingliyouxuan.cn',
                        'imageDomain'=>'image.lingliyouxuan.cn'
                    ]
                ]
            ],
            'wxc8414d0b065b0485'=>[
                'appId'=>'wxc8414d0b065b0485',
                'type'=>'mini',
                'env'=>[
                    'test'=>[
                        'businessDomain'=>'alliance.lingliyouxuan.cn',
                        'imageDomain'=>'image.lingliyouxuan.cn'
                    ],
                    'release'=>[
                        'businessDomain'=>'alliance.lingliyouxuan.cn',
                        'imageDomain'=>'image.lingliyouxuan.cn'
                    ]
                ]
            ],
        ];
    }
}