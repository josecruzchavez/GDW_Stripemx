<?php
namespace GDW\Stripemx\Model;

use GDW\Stripemx\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;

class StripemxCard
{
    protected $enc;
    protected $help;
    protected $logger;

    public function __construct(
        Data $help, 
        EncryptorInterface $enc
    )
    {
        $this->enc = $enc;
        $this->help = $help;
    }

    public function enable()
    {
        return $this->help->getDirectVal('active');
    }

    public function isDebug()
    {
        return $this->help->getDirectVal('debug');
    }


    public function note()
    {
        return $this->help->getDirectVal('note');
    }

    public function notemsi()
    {
        return $this->help->getDirectVal('note_msi');
    }

    public function globalerrorshow()
    {
        return $this->help->getDirectVal('global_error_show');
    }

    public function sandbox()
    {
        return $this->help->getDirectVal('sandbox_mode');
    }

    public function keyPublic()
    {
        return $this->sandbox() ? $this->help->getDirectVal('key_public_sandbox') : $this->help->getDirectVal('key_public_live');
    }

    public function keySecret()
    {
        $getToken = $this->sandbox() ? $this->help->getDirectVal('key_secret_sandbox') : $this->help->getDirectVal('key_secret_live');
        return $this->enc->decrypt($getToken);
    }

    public function getCoutas()
    {
        return $this->help->getDirectVal('payment_limit');
    }

    public function getStripeScript()
    {
        return $this->help->getDirectVal('stripe_script') ?? null;
    }

    public function setLogs($desc, $data)
    {
        if($this->isDebug()){
            try {
                $this->help->log($desc);
                $this->help->log($data);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

}
