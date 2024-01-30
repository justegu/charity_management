<?php

include 'Charity.php';
include 'Donation.php';

class CharityException extends Exception {}
class DonationException extends Exception {}

class CharityManager
{
    private $charities = [];
    private $donations = [];


//    Charities CRUD
    public function viewCharities() {
        foreach ($this->charities as $charity) {
            echo "ID: {$charity->getId()}, Name: {$charity->getName()}, Email: {$charity->getEmail()}\n";
        }
    }

    public function addCharity(Charity $charity) {
        try {
            $this->validateAddCharity($charity);
            $this->charities[] = $charity;
            echo "Charity added successfully.\n";
        } catch (CharityException $e) {
            echo "--- Error: {$e->getMessage()} ---\n";
        }
    }

    public function editCharity($charityId, Charity $newCharity) {

        if ($newCharity->getId() !== null &&
            $newCharity->getId() !== "" &&
            $newCharity->getId() > 0 &&
            $newCharity->getName() !== null &&
            $newCharity->getName() !== ""
        ) {
            if ($this->isValidEmail($newCharity)) {
                foreach ($this->charities as &$charity) {
                    if ($charity->getId() === $charityId) {
                        if ($newCharity->getId() === $charityId) {
                            $charity = $newCharity;
                            echo "Charity edited successfully.\n";
                            return;
                        } else {
                            echo "Error: Provided charityId does not match the ID of the charity being edited.\n";
                            return;
                        }
                    }
                }
                echo "Error: Charity with ID {$charityId} not found.\n";
            } else {
                echo "Error: Invalid email address.\n";
            }
        } else {
            echo "Error: All required fields must be filled correctly.\n";
        }
    }

    public function deleteCharity($charityId) {
        foreach ($this->charities as $key => $charity) {
            if ($charity->getId() === $charityId) {
                unset($this->charities[$key]);
                echo "Charity deleted successfully.\n";
                return;
            }
        }
        echo "Error: Charity with ID {$charityId} not found.\n";
    }

//    view charities with donations
    public function viewCharitiesWithDonations() {
        foreach ($this->charities as $charity) {
            echo "ID: {$charity->getId()}, Name: {$charity->getName()}, Email: {$charity->getEmail()}\n";

            $donations = $this->getDonationsForCharity($charity->getId());
            if (!empty($donations)) {
                echo "  Donations:\n";
                foreach ($donations as $donation) {
                    echo "    ID: {$donation->getId()}, Donor name: {$donation->getDonorName()}, Amount: {$donation->getAmount()}, Date: {$donation->getDateTime()}\n";
                }
            } else {
                echo "  No donations for this charity.\n";
            }

            echo "\n";
        }
    }

    private function getDonationsForCharity($charityId) {
        $donationsForCharity = [];
        foreach ($this->donations as $donation) {
            if ($donation->getCharityId() === $charityId) {
                $donationsForCharity[] = $donation;
            }
        }
        return $donationsForCharity;
    }

//   charities import from CSV
    public function importCharitiesFromCSV($csvFilePath) {
        $csvData = array_map('str_getcsv', file($csvFilePath));

        foreach ($csvData as $row) {
            $id = (int)$row[0];
            $name = $row[1];
            $email = $row[2];

            while ($this->isExistingCharityId($id)) {
                $id++;
            }
            if ($this->isExistingCharityEmail($email)) {
                echo "Error: Charity '{$name}' matches already existing charity with the same email. Skipping import.\n";
                continue;
            }
            $this->addCharity(new Charity($id, $name, $email));
        }
        echo "Charities imported successfully from CSV.\n";
    }

//   validation
    private function validateAddCharity(Charity $charity) {
        if ($this->isExistingCharityId($charity->getId())) {
            throw new CharityException("Charity with ID {$charity->getId()} already exists.");
        } elseif ($this->isExistingCharityEmail($charity->getEmail())) {
            throw new CharityException("Charity with email {$charity->getEmail()} already exists.");
        } elseif ($charity->getId() !== null &&
            $charity->getId() !== "" &&
            $charity->getId() > 0 &&
            $charity->getName() !== null &&
            $charity->getName() !== "") {
            if (!$this->isValidEmail($charity)) {
                throw new CharityException("Invalid email address.");
            }
        } else {
            throw new CharityException("All required fields must be filled.");
        }
    }

    private function isValidEmail(Charity $charity) {
        return filter_var($charity->getEmail(), FILTER_VALIDATE_EMAIL);
    }

    private function isExistingCharityId($charityId) {
        foreach ($this->charities as $charity) {
            if ($charity->getId() === $charityId) {
                return true;
            }
        }
        return false;
    }
    private function isExistingCharityEmail($email) {
        foreach ($this->charities as $charity) {
            if ($charity->getEmail() === $email) {
                return true;
            }
        }
        return false;
    }

//    Donations CRUD
    public function viewDonations() {
        foreach ($this->donations as $donation) {
            echo "ID: {$donation->getId()}, Donor name: {$donation->getDonorName()}, Amount: {$donation->getAmount()}, Charity id: {$donation->getCharityId()}, Date: {$donation->getDateTime()}\n";
        }
    }

    public function addDonation(Donation $donation) {
        try {
            $this->validateAddDonation($donation);
            $this->donations[] = $donation;
            echo "Donation logged successfully.\n";
        } catch (DonationException $e) {
            echo "--- Error: {$e->getMessage()} ---\n";
        }
    }

//   validation
    private function validateAddDonation(Donation $donation) {
        if ($this->isExistingDonationId($donation->getId())) {
            throw new DonationException("Donation with ID {$donation->getId()} already exists.");
        } elseif($donation->getId() !== null &&
            $donation->getId() !== "" &&
            $donation->getId() > 0 &&
            $donation->getDonorName() !== null &&
            $donation->getDonorName() !== "" &&
            $donation->getAmount() !== null &&
            $donation->getAmount() !== "" &&
            $donation->getAmount() > 0 &&
            $donation->getCharityId() !== null &&
            $donation->getCharityId() !== "" &&
            $donation->getCharityId() > 0 &&
            $donation->getDateTime() !== null) {
            $charityIdExists = false;
            foreach ($this->charities as $charity) {
                if ($charity->getId() === $donation->getCharityId()) {
                    $charityIdExists = true;
                    break;
                }
            }
            if (!$charityIdExists) {
                throw new DonationException("Charity with ID {$donation->getCharityId()} not found. Donation not added.");
            }
        } else {
            throw new DonationException("All required fields must be filled.");
        }
    }

    private function isExistingDonationId($donationId) {
        foreach ($this->donations as $donation) {
            if ($donation->getId() === $donationId) {
                return true;
            }
        }
        return false;
    }
}


$charityManager = new CharityManager();

echo "------------------------------------------------------\n";
// Adding some charities
$charitiesData = [
    [1, 'Charity A', 'charityA@example.com'],
    [2, 'Charity B', 'charityB@example.com'],
    [3, 'Charity C', 'charityC@example.com'],
    [4, 'Charity D', 'charityD@example'],
    [5, 'Charity E', 'charityE@example.com'],
    [6, '', 'charityF@example.com'],
    [null, 'Charity G', 'charityG@example.com'],
    ["", 'Charity H', 'charityH@example.com'],
    [9, 'Charity I', ''],
];
foreach ($charitiesData as $charityData) {
    list($id, $name, $email) = $charityData;
    $charity = new Charity($id, $name, $email);
    $charityManager->addCharity($charity);
}

// Viewing charities
echo "----------------- Viewing Charities: -----------------\n";
$charityManager->viewCharities();
echo PHP_EOL;

echo "-------------------------------------------------------------------\n";
// Editing charity
$updatedCharity = new Charity(1, 'Updated Charity A', 'UpdatedCharityA@example.com');
$charityManager->editCharity(1, $updatedCharity);
$updatedCharity = new Charity(5, 'Updated Charity E', 'UpdatedCharityE@example.com');
$charityManager->editCharity(50, $updatedCharity);
$updatedCharity = new Charity(3, 'Updated Charity C', 'UpdatedCharityC@example.com');
$charityManager->editCharity(2, $updatedCharity);

// Viewing charities after edit
echo "------------------ Viewing Charities after Edit: ------------------\n";
$charityManager->viewCharities();
echo PHP_EOL;

echo "-------------------------------------------------------------------\n";
// Deleting charity
$charityManager->deleteCharity(2);
$charityManager->deleteCharity(20);

// Viewing charities after delete
echo "----------------- Viewing Charities after Delete: -----------------\n";
$charityManager->viewCharities();
echo PHP_EOL;

echo "------------------------------------------------------------------------------------------------\n";
// Importing charities from CSV
$charityManager->importCharitiesFromCSV('charities.csv');

echo "---------------------------- Viewing Charities after import from csv: ---------------------------\n";
$charityManager->viewCharities();
echo PHP_EOL;

echo "---------------------------------------------------------------------------------\n";
// Logging donation
$donationsData = [
    [1, 'John McWeen', 100, 1, date('Y-m-d H:i:s')],
    [2, 'Laura Smith', 450, 3, date('Y-m-d H:i:s')],
    [3, 'Kyle Miller', 300, 5, date('Y-m-d H:i:s')],
    [4, 'Mike Sandler', 10, 2, date('Y-m-d H:i:s')],
    [2, 'Julia Roberts', 260, 3, date('Y-m-d H:i:s')],
    [6, 'Sandra Bulock', 1180, 5, date('Y-m-d H:i:s')],
    [7, 'Sandra Bulock', 500, 50, date('Y-m-d H:i:s')]
];

foreach ($donationsData as $donationData) {
    list($id, $donorName, $amount, $charityId, $dateTime) = $donationData;
    $donation = new Donation($id, $donorName, $amount, $charityId, $dateTime);
    $charityManager->addDonation($donation);
}

// Viewing donations
echo "---------------------------------- Viewing Donations: ----------------------------------\n";
$charityManager->viewDonations();
echo PHP_EOL;


// Viewing charities with donations
echo "----------------- Viewing Charities with Donations: -----------------\n";
$charityManager->viewCharitiesWithDonations();
echo PHP_EOL;