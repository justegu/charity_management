<?php

class Donation {
    private $id;
    private $donorName;
    private $amount;
    private $charityId;
    private $dateTime;

    public function __construct($id, $donorName, $amount, $charityId, $dateTime) {
        $this->id = $id;
        $this->donorName = $donorName;
        $this->amount = $amount;
        $this->charityId = $charityId;
        $this->dateTime = $dateTime;
    }

    // Getters and setters for Donation properties
    public function getId() {
        return $this->id;
    }

    public function getDonorName() {
        return $this->donorName;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getCharityId() {
        return $this->charityId;
    }

    public function getDateTime() {
        return $this->dateTime;
    }
}