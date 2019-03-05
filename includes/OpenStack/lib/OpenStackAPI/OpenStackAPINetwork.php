<?php

//Decoded by SoarTeam SoarTeam
class OpenStackAPINetwork extends OpenStackAPIRequest
{
	const USEENDPOINT = 'network';
	const VERSION = 'v2.0';
	const PORT = 9696;
	public function listNetworks($tenantID = false)
	{
		$params = array();
		if ($tenantID) {
			$params['tenant_id'] = $tenantID;
		}
		$response = $this->_GET('networks', $params);
		$networks = array();
		foreach ($response['networks'] as $network) {
			$networks[$network['id']] = array('id' => $network['id'], 'name' => $network['name'], 'status' => $network['status'], 'ownerID' => $network['tenant_id'], 'external' => $network['router:external'], 'subNets' => array());
			foreach ($network['subnets'] as $sub) {
				$networks[$network['id']]['subNets'][] = $sub;
			}
		}
		return $networks;
	}
	public function createRouter($name, $externalNetworkUUID = false)
	{
		$params = array('router' => array('name' => $name));
		if ($externalNetworkUUID) {
			$params['router']['external_gateway_info'] = array('network_id' => $externalNetworkUUID);
		}
		$response = $this->_POST(array('routers'), $params);
		return array('id' => $response['router']['id']);
	}
	public function listRouters($tenantID = false)
	{
		$params = array();
		if ($tenantID) {
			$params['tenant_id'] = $tenantID;
		}
		$response = $this->_GET('routers', $params);
		$routers = array();
		foreach ($response['routers'] as $router) {
			$routers[] = array('id' => $router['id'], 'name' => $router['name'], 'ownerID' => $router['tenant_id'], 'externalNetwork' => !empty($router['external_gateway_info']['network_id']) ? $router['external_gateway_info']['network_id'] : false);
		}
		return $routers;
	}
	public function createNetwork($name, $shared = false)
	{
		$response = $this->_POST('networks', array('network' => array('name' => $name, 'shared' => $shared)));
		return array('id' => $response['network']['id']);
	}
	public function createSubNet($networkUUID, $cidr, $ipVersion = '4')
	{
		$response = $this->_POST('subnets', array('subnet' => array('network_id' => $networkUUID, 'cidr' => $cidr, 'ip_version' => $ipVersion)));
		return array('id' => $response['subnet']['id']);
	}
	public function addSubNetToRouter($routerID, $subNetID)
	{
		$this->_PUT(array('routers' => $routerID, 0 => 'add_router_interface'), array('subnet_id' => $subNetID));
		return true;
	}
	public function listFloatingIP($tenantID = NULL)
	{
		$params = array();
		if ($tenantID) {
			$params['tenant_id'] = $tenantID;
		}
		$response = $this->_GET('floatingips');
		$floatingIPs = array();
		foreach ($response['floatingips'] as $ip) {
			$floatingIPs[$ip['id']] = array('id' => $ip['id'], 'address' => $ip['floating_ip_address'], 'ownerID' => $ip['tenant_id'], 'networkID' => $ip['floating_network_id']);
		}
		return $floatingIPs;
	}
	public function createFloatingIP($networkID)
	{
		$response = $this->_POST(array('floatingips'), array('floatingip' => array('floating_network_id' => $networkID)));
		return array('id' => $response['floatingip']['id'], 'address' => $response['floatingip']['floating_ip_address']);
	}
	public function listPorts($tenantID = NULL)
	{
		$params = array();
		if ($tenantID) {
			$params['tenant_id'] = $tenantID;
		}
		$response = $this->_GET('ports', $params);
		$output = array();
		foreach ($response['ports'] as $port) {
			$output[$port['id']] = array('id' => $port['id'], 'device_id' => $port['device_id'], 'network_id' => $port['network_id'], 'subnets' => array());
			foreach ($port['fixed_ips'] as $fixed) {
				$output[$port['id']]['subnets'][] = $fixed['subnet_id'];
			}
		}
		return $output;
	}
	public function createPort($networkID)
	{
		$response = $this->_POST('ports', array('port' => array('network_id' => $networkID)));
		return array('id' => $response['port']['id'], 'status' => $response['port']['status']);
	}
	public function getQuota($tenantID)
	{
		$response = $this->_GET(array('quotas' => $tenantID));
		return array('subnet' => $response['quota']['subnet'], 'network' => $response['quota']['network'], 'floatingip' => $response['quota']['floatingip'], 'router' => $response['quota']['router'], 'port' => $response['quota']['port']);
	}
	public function setQuota($tenantID, $data)
	{
		$this->_PUT(array('quotas' => $tenantID), array('quota' => array('subnet' => $data['subnet'], 'network' => $data['network'], 'floatingip' => $data['floating_ips'], 'router' => $data['router'], 'port' => $data['port'])));
		return true;
	}
	public function listSubnets($tenantID = false)
	{
		$params = array();
		if ($tenantID) {
			$params['tenant_id'] = $tenantID;
		}
		$response = $this->_GET('subnets', $params);
		$output = array();
		foreach ($response['subnets'] as $subnet) {
			$output[$subnet['id']] = array('UUID' => $subnet['id'], 'name' => $subnet['name'], 'cidr' => $subnet['cidr']);
		}
		return $output;
	}
}