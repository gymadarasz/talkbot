<?php

namespace Madsoft\Library;

class Redirector {
    protected Config $config;
    
    /**
     * 
     * @param Config $config
     * @param Session $session
     */
    public function __construct(Config $config, Session $session) {
        $this->config = $config;
        $this->session = $session;
    }
    
    /**
     * 
     * @param string $target
     * @return void
     */
    public function getRedirectResponse(string $target): string {
        $redirect = $this->config->get('Site')->get('base') 
                . '/?' . Router::ROUTE_QUERY_KEY . '=' . $target;
        $this->session->set(
            'message',
            [
                'type' => 'success',
                'text' => 'Logout sucess',
            ]
        );
        header("Location: $redirect");
        return "<script>document.location.href = '$redirect';</script>";
    }
}
