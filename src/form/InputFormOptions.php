<?php

namespace bdesprez\psmodulefwk\form;

use PrestaShopModuleException;

class InputFormOptions
{
    /**
     * Liste de valeurs
     * Chaque valeur doit au moins avoir les clefs $idKey et $valueKey
     * @var array
     */
    private $query = [];
    private $idKey;
    private $valueKey;
    /**
     * Tableau associatif id => value
     * @var array
     */
    private $values = [];

    /**
     * Values constructor.
     * @param array $query
     * @param string $idKey
     * @param string $valueKey
     * @throws PrestaShopModuleException
     */
    public function __construct(array $query, $idKey = 'id', $valueKey = 'value')
    {
        foreach ($query as $row) {
            if (!array_key_exists($idKey, $row) || !array_key_exists($valueKey, $row)) {
                throw new PrestaShopModuleException("Query results has to contain at least the keys \"{$idKey}\" and \"{$valueKey}\" !");
            }
            $this->values[$row[$this->idKey]] = $row[$this->valueKey];
        }
        $this->query = $query;
        $this->idKey = $idKey;
        $this->valueKey = $valueKey;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getIdKey()
    {
        return $this->idKey;
    }

    /**
     * @return string
     */
    public function getValueKey()
    {
        return $this->valueKey;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function toPrestaShopFormat()
    {
        return [
            'query' => $this->query,
            'id' => $this->idKey,
            'name' => $this->valueKey
        ];
    }

}
