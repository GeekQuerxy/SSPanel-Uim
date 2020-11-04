<?php

namespace App\Controllers\User;

use App\Controllers\UserController;
use App\Models\{
    Node,
    NodeInfoLog,
    NodeOnlineLog,
    User,
    Relay
};
use App\Utils\{
    URL,
    Tools,
    Radius,
};
use Slim\Http\{
    Request,
    Response
};
use Psr\Http\Message\ResponseInterface;

/**
 *  User NodeController
 */
class NodeController extends UserController
{
    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function node($request, $response, $args): ResponseInterface
    {
        $user  = $this->user;
        $nodes = Node::where('type', 1)
            ->orderBy('node_class')
            ->orderBy('name')
            ->get();

        // 判断用户的协议可否中转
        if (!Tools::is_protocol_relay($user)) {
            $relay_rules = [];
        } else {
            $relay_rules = Relay::where('user_id', $user->id)
                ->orwhere('user_id', 0)
                ->orderBy('id', 'asc')
                ->get();
        }

        $array_nodes  = [];
        $nodes_muport = [];
        foreach ($nodes as $node) {
            if (!$user->is_admin && $node->node_group != $user->node_group && $node->node_group != 0) {
                // 如果用户不是管理员、并且用户的分组不等于节点的分组、并且节点的分组不等于 0
                continue;
            }

            if ($node->sort == 9) {
                $mu_user = User::where('port', '=', $node->server)->first();
                if ($mu_user != null) {
                    $mu_user->obfs_param = $user->getMuMd5();
                    $nodes_muport[]      = [
                        'server' => $node,
                        'user'   => $mu_user
                    ];
                }
                continue;
            }

            $array_node               = [];
            $array_node['raw_node']   = $node;
            $array_node['id']         = $node->id;
            $array_node['class']      = $node->node_class;
            $array_node['name']       = $node->name;
            $array_node['sort']       = $node->sort;
            $array_node['info']       = $node->info;
            $array_node['mu_only']    = $node->mu_only;
            $array_node['group']      = $node->node_group;

            if ($node->sort == 13) {
                $server = Tools::ssv2Array($node->server);
                $array_node['server'] = $server['add'];
            } else {
                $array_node['server'] = $node->getServer();
            }

            // 匹配节点国旗
            $regex   = $_ENV['flag_regex'];
            $matches = [];
            preg_match($regex, $node->name, $matches);
            if (isset($matches[0])) {
                $array_node['flag'] = $matches[0] . '.png';
            } else {
                $array_node['flag'] = 'unknown.png';
            }

            // 节点在线用户数
            $array_node['online_user'] = -1;
            if (in_array($node->sort, [0, 7, 8, 10, 11, 12, 13, 14])) {
                $onlineLog = NodeOnlineLog::where('node_id', $node->id)
                    ->where('log_time', '>', time() - 300)
                    ->orderBy('id', 'desc')
                    ->first();
                $array_node['online_user'] = $onlineLog != null ? $onlineLog->online_user : 0;
            }

            // 节点在线状态
            // 0: new node; -1: offline; 1: online
            $node_heartbeat = $node->node_heartbeat + 300;
            if (in_array($node->sort, [0, 7, 8, 10, 11, 12, 13, 14])) {
                if ($node_heartbeat > time()) {
                    $array_node['online'] = 1;
                }
                if ($node_heartbeat < time()) {
                    $array_node['online'] = -1;
                }
                if ($node_heartbeat == 300) {
                    $array_node['online'] = 0;
                }
            } else {
                $array_node['online'] = 0;
            }

            // 节点负载
            $infoLog = NodeInfoLog::where('node_id', $node->id)
                ->where('log_time', '>', time() - 300)
                ->orderBy('id', 'desc')
                ->first();
            $array_node['latest_load'] = $infoLog != null ? explode(' ', $infoLog->load)[0] * 100 : -1;

            // 节点已用流量
            $array_node['traffic_used']  = (int) Tools::flowToGB($node->node_bandwidth);

            // 节点总流量
            $array_node['traffic_limit'] = (int) Tools::flowToGB($node->node_bandwidth_limit);

            // 节点速率
            if ($node->node_speedlimit == 0.0) {
                $array_node['bandwidth'] = 0;
            } elseif ($node->node_speedlimit >= 1024.00) {
                $array_node['bandwidth'] = round($node->node_speedlimit / 1024.00, 1) . 'Gbps';
            } else {
                $array_node['bandwidth'] = $node->node_speedlimit . 'Mbps';
            }

            // 节点流量倍率
            $array_node['traffic_rate'] = $node->traffic_rate;
            // 节点状态
            $array_node['status']       = $node->status;

            $array_nodes[] = $array_node;
        }

        return $response->write(
            $this->view()
                ->assign('nodes', $array_nodes)
                ->assign('nodes_muport', $nodes_muport)
                ->assign('relay_rules', $relay_rules)
                ->assign('tools', new Tools())
                ->assign('user', $user)
                ->registerClass('URL', URL::class)
                ->display('user/node.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function nodeAjax($request, $response, $args): ResponseInterface
    {
        $id           = $args['id'];
        $point_node   = Node::find($id);
        $prefix       = explode(' - ', $point_node->name);
        return $response->write(
            $this->view()
                ->assign('point_node', $point_node)
                ->assign('prefix', $prefix[0])
                ->assign('id', $id)
                ->display('user/nodeajax.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function nodeInfo($request, $response, $args)
    {
        $user          = $this->user;
        $id            = $args['id'];
        $mu            = $request->getQueryParams()['ismu'];
        $relay_rule_id = $request->getQueryParams()['relay_rule'];
        $node          = Node::find($id);
        if ($node == null) {
            return null;
        }

        $isAvailable = function ($user, $node) {
            return $user->class >= $node->node_class && ($user->node_group == $node->node_group || $node->node_group == 0);
        };

        switch ($node->sort) {
            case 0:
            case 10:
            case 13:
                if (
                    ($isAvailable($user, $node) || $user->is_admin)
                    &&
                    ($node->node_bandwidth_limit == 0 || $node->node_bandwidth < $node->node_bandwidth_limit)
                ) {
                    return $this->view()
                        ->assign('node', $node)
                        ->assign('user', $user)
                        ->assign('mu', $mu)
                        ->assign('relay_rule_id', $relay_rule_id)
                        ->registerClass('URL', URL::class)
                        ->display('user/nodeinfo.tpl');
                }
                break;
            case 1:
                if ($isAvailable($user, $node)) {
                    $email = $this->user->email;
                    $email = Radius::GetUserName($email);
                    $json_show = 'VPN 信息'
                        . '<br>地址：' . $node->server
                        . '<br>用户名：' . $email
                        . '<br>密码：' . $this->user->passwd
                        . '<br>支持方式：' . $node->method
                        . '<br>备注：' . $node->info;
                    return $this->view()
                        ->assign('json_show', $json_show)
                        ->display('user/nodeinfovpn.tpl');
                }
                break;
            case 2:
                if ($isAvailable($user, $node)) {
                    $email = $this->user->email;
                    $email = Radius::GetUserName($email);
                    $json_show = 'SSH 信息'
                        . '<br>地址：' . $node->server
                        . '<br>用户名：' . $email
                        . '<br>密码：' . $this->user->passwd
                        . '<br>支持方式：' . $node->method
                        . '<br>备注：' . $node->info;
                    return $this->view()
                        ->assign('json_show', $json_show)
                        ->display('user/nodeinfossh.tpl');
                }
                break;
            case 5:
                if ($isAvailable($user, $node)) {
                    $email = $this->user->email;
                    $email = Radius::GetUserName($email);
                    $json_show = 'Anyconnect 信息'
                        . '<br>地址：' . $node->server
                        . '<br>用户名：' . $email
                        . '<br>密码：' . $this->user->passwd
                        . '<br>支持方式：' . $node->method
                        . '<br>备注：' . $node->info;
                    return $this->view()
                        ->assign('json_show', $json_show)
                        ->display('user/nodeinfoanyconnect.tpl');
                }
                break;
            default:
                echo '微笑';
        }
    }
}
