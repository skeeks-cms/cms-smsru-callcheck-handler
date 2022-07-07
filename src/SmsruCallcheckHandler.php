<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\callcheck\smsru;

use skeeks\cms\callcheck\CallcheckHandler;
use skeeks\cms\models\CmsCallcheckMessage;
use skeeks\yii2\form\fields\FieldSet;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 *
 * @see https://smsimple.ru/api-http/
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SmsruCallcheckHandler extends CallcheckHandler
{
    public $api_key = "";
    public $sender = "";

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'sms.ru'),
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['api_key'], 'required'],
            [['api_key'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'api_key' => "API ключ",

        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [

        ]);
    }


    /**
     * @return array
     */
    public function getConfigFormFields()
    {
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Основные',
                'fields' => [
                    'api_key',
                ],
            ],
        ];
    }


    /**
     * @see https://sms.ru/api/code_call
     *
     * @param $phone
     * @return array
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function callcheck($phone)
    {
        $queryString = http_build_query([
            'api_id'     => $this->api_key,
            'phone'      => $phone,
            'ip'         => \Yii::$app->request->userIP,
            'partner_id' => 145700,
        ]);

        $url = 'https://sms.ru/code/call?'.$queryString;


        $client = new Client();
        $response = $client
            ->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($url)
            ->send();

        if (!$response->isOk) {
            throw new Exception($response->content);
        }

        return $response->data;
    }

    /**
     * @param CmsCallcheckMessage $callcheckMessage
     * @return bool
     * @throws Exception
     */
    public function callcheckMessage(CmsCallcheckMessage $callcheckMessage)
    {
        $data = $this->callcheck($callcheckMessage->phone);

        $callcheckMessage->provider_response_data = (array)$data;
        $callcheckMessage->provider_status = (string)ArrayHelper::getValue($data, 'status');
        $callcheckMessage->provider_call_id = (string)ArrayHelper::getValue($data, 'call_id');

        if (ArrayHelper::getValue($data, 'status') == "OK") {
            $callcheckMessage->status = CmsCallcheckMessage::STATUS_OK;
            $callcheckMessage->code = ArrayHelper::getValue($data, 'code');
        } else {
            $callcheckMessage->status = CmsCallcheckMessage::STATUS_ERROR;
            $callcheckMessage->error_message = ArrayHelper::getValue($data, 'status_text');
        }

        return true;
    }

}