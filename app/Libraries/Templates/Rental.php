<?php

namespace App\Libraries\Templates;

use App\Interfaces\Template;
use App\Libraries\Helper;
use App\Libraries\Render;
use App\Libraries\TemplateManager;
use App\Models\Customer;
use App\Models\Firm;
use App\Models\Rental as RentalModel;
use App\Traits\TemplateVariablesTrait;

class Rental extends TemplateManager implements Template
{
    use TemplateVariablesTrait;

    public static function getType()
    {
        return "rental";
    }

    public static function getName()
    {
        return "Dokument umowy najmu";
    }

    public static function getClassObject()
    {
        return \App\Models\Rental::class;
    }

    public function getFilename()
    {
        return "rental-" . $this->getObject()->full_number . ".pdf";
    }

    public function getTitle()
    {
        return "Kosztorys: " . $this->getObject()->full_number;
    }

    public static function getAvailableVars($array = false, $global = true)
    {
        $variables = [
            "fields" => [
                "numer" => ["Numer umowy", "full_number"],
                "data_umowy" => ["Data umowy", "document_date"],
                "najemca_nazwa" => ["Nazwa najemcy", "tenant.name"],
                "najemca_adres" => ["Adres najemcy", "tenant.address"],
                "najemca_dokument_rodzaj" => ["Dokument najemcy - nazwa", "tenant.document_type"],
                "najemca_dokument_numer" => ["Dokument najemcy - numer", "tenant.document_number"],
                "najemca_nip" => ["NIP najemcy", "tenant.nip"],
                "najemca_regon" => ["Regon najemcy", "tenant.regon"],
                "najemca_pesel" => ["Pesel najemcy", "tenant.pesel"],
                "lokal_powierzchnia" => ["Powierzchnia lokalu", "item.area"],
                "lokal_ilosc_pomieszczen" => ["Ilość pokoi", "item.num_rooms"],
                "lokal_adres" => ["Adres lokalu", "item.address"],
                "kaucja" => ["Kaucja", "deposit"],
                "czynsz" => ["Czynsz", "rent"],
                "dzien_platnosci" => ["Dzień płatności", "payment_day"],
                "umowa_okres_od" => ["Początek wynajmu", "period_start"],
                "umowa_okres_do" => ["Koniec wynajmu", "period_end"],
                "okres_wypowiedzenia" => ["Okres wypowiedzenia", "termination_period"],
                "wlasciciel_nazwa" => ["Nazwa właściciela", "owner.name"],
                "wlasciciel_adres" => ["Adres właściciela", "owner.address"],
                "wlasciciel_dokument_rodzaj" => ["Dokument właściciela - nazwa", "owner.document_type"],
                "wlasciciel_dokument_numer" => ["Dokument właściciela - numer", "owner.document_number"],
                "wlasciciel_nip" => ["NIP właściciela", "owner.nip"],
                "wlasciciel_regon" => ["Regon właściciela", "owner.regon"],
            ]
        ];

        if($array)
            return self::availableVarsToArray($variables);

        return $variables;
    }

    public function getData()
    {
        $out = [];
        $object = $this->getObject();
        $out = $object->toArray();
        $out["comments"] = nl2br($out["comments"]);
        
        $out["period_start"] = $object->start;
        $out["period_end"] = $object->period == RentalModel::PERIOD_INDETERMINATE ? " na czas nieokreślony" : $object->end;
        
        if($object->termination_period == RentalModel::PERIOD_TERM_MONTHS)
            $out["termination_period"] = $object->termination_months . " miesięcznym";
        if($object->termination_period == RentalModel::PERIOD_TERM_DAYS)
            $out["termination_period"] = $object->termination_days . " dniowym";
        
        $tenant = $object->getTenant();
        $out = array_merge($out, $this->addPrefixToArrayKeys("tenant", $tenant->toArray()));
        $out["tenant.address"] = Helper::generateAddress($tenant, ", ");
        $out["tenant.document_type"] = mb_strtolower(Customer::getDocumentTypes()[$out["tenant.document_type"]] ?? $out["tenant.document_type"]);
        
        $item = $object->getItem();
        $out = array_merge($out, $this->addPrefixToArrayKeys("item", $item->toArray()));
        $out["item.address"] = Helper::generateAddress($item, ", ");
        
        $owner = $item->getOwner();
        if($owner instanceof Customer)
        {
            $out = array_merge($out, $this->addPrefixToArrayKeys("owner", $owner->toArray()));
            $out["owner.address"] = Helper::generateAddress($owner, ", ");
            $out["owner.document_type"] = mb_strtolower(Customer::getDocumentTypes()[$out["owner.document_type"]] ?? $out["owner.document_type"]);
        }
        elseif($owner)
        {
            $out = array_merge($out, $this->addPrefixToArrayKeys("owner", (array)$owner));
            $out["owner.address"] = Helper::generateAddress($owner, ", ");
            $out["owner.document_type"] = mb_strtolower(Customer::getDocumentTypes()[$out["owner.document_type"]] ?? $out["owner.document_type"]);
        }

        return $out;
    }
}
