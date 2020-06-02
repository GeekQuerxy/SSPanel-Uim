<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Models\GConfig;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class GConfigController extends AdminController
{
    /**
     * @var array
     */
    protected $total_column = [
        'op'             => '操作',
        'name'           => '配置名称',
        'key'            => '配置名',
        'value'          => '配置值',
        'operator_id'    => '操作员 ID',
        'operator_name'  => '操作员名称',
        'operator_email' => '操作员邮箱',
        'last_update'    => '修改时间'
    ];

    /**
     * @var array
     */
    protected $default_show_column = [
        'op', 'name', 'value', 'last_update'
    ];

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function update($request, $response, $args): ResponseInterface
    {
        $key    = trim($args['key']);
        $user   = $this->user;
        $config = GConfig::where('key', '=', $key)->first();
        if ($config != null && $config->setValue($request->getParam('value'), $user) === true) {
            $result = [
                'ret' => 1,
                'msg' => '修改成功'
            ];
        } else {
            $result = [
                'ret' => 0,
                'msg' => '修改失败'
            ];
        }
        return $response->withJson($result);
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function edit($request, $response, $args): ResponseInterface
    {
        $key    = trim($args['key']);
        $config = GConfig::where('key', '=', $key)->first();
        return $response->write(
            $this->view()
                ->assign('edit_config', $config)
                ->fetch('admin/config/edit.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function config_ajax($request, $response, $args, $key): array
    {
        $start        = $request->getParam("start");
        $limit_length = $request->getParam('length');
        $configs      = GConfig::skip($start)->where('key', 'LIKE', "%$key%")->limit($limit_length)->get();
        $total_conut  = GConfig::where('key', 'LIKE', "%$key%")->count();
        $data         = [];
        foreach ($configs as $config) {
            $tempdata = [];
            $tempdata['op']             = '<a class="btn btn-brand" href="/admin/config/update/' . $config->key . '/edit">编辑</a>';
            $tempdata['name']           = $config->name;
            $tempdata['key']            = $config->key;
            $tempdata['value']          = $config->getValue();
            $tempdata['operator_id']    = $config->operator_id;
            $tempdata['operator_name']  = $config->operator_name;
            $tempdata['operator_email'] = $config->operator_email;
            $tempdata['last_update']    = date('Y-m-d H:i:s', $config->last_update);
            if (strpos($config->key, '.bool.')) {
                $tempdata['value'] = ($config->getValue() ? '开启' : '关闭');
            } else {
                $tempdata['value'] = '(请在编辑页面查看)';
            }
            $data[] = $tempdata;
        }
        $info = [
            'draw'            => $request->getParam('draw'),
            'recordsTotal'    => $total_conut,
            'recordsFiltered' => $total_conut,
            'data'            => $data
        ];
        return $info;
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function register($request, $response, $args): ResponseInterface
    {
        $table_config['total_column']        = $this->total_column;
        $table_config['default_show_column'] = $this->default_show_column;
        $table_config['ajax_url']            = 'register/ajax';
        $edit_config = GConfig::where('key', '=', 'Register.string.Mode')->first();
        return $response->write(
            $this->view()
                ->assign('edit_config', $edit_config)
                ->assign('table_config', $table_config)
                ->fetch('admin/config/user/register.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function register_ajax($request, $response, $args): ResponseInterface
    {
        return $response->withJson($this->config_ajax($request, $response, $args, 'Register'));
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function telegram($request, $response, $args): ResponseInterface
    {
        $table_config['total_column']        = $this->total_column;
        $table_config['default_show_column'] = $this->default_show_column;
        $table_config['ajax_url']            = 'telegram/ajax';
        return $response->write(
            $this->view()
                ->assign('table_config', $table_config)
                ->fetch('admin/config/telegram/index.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function telegram_ajax($request, $response, $args): ResponseInterface
    {
        return $response->withJson($this->config_ajax($request, $response, $args, 'Telegram'));
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function users($request, $response, $args): ResponseInterface
    {
        $table_config['total_column']        = $this->total_column;
        $table_config['default_show_column'] = $this->default_show_column;
        $table_config['ajax_url']            = 'users/ajax';
        return $response->write(
            $this->view()
                ->assign('table_config', $table_config)
                ->fetch('admin/config/user/index.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function users_ajax($request, $response, $args): ResponseInterface
    {
        return $response->withJson($this->config_ajax($request, $response, $args, 'Users'));
    }
}
