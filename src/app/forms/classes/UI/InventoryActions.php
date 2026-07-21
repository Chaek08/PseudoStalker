<?php
namespace app\forms\classes\UI;

class InventoryActions
{
    private $inventory;

    public function __construct($inventory)
    {
        $this->inventory = $inventory;
    }

    public function useItem(): void
    {
        $inv = $this->inventory;
        $item = $inv->getSelectedItem();

        if (!$item)
        {
            return;
        }

        $inv->UseSlotSound();
        $inv->HideCombobox();

        switch ($item->getId())
        {
            case 'medkit':
                $this->applyMedkitEffect();

                $item->setCount($item->getCount() - 1);

                if ($item->getCount() < 1)
                {
                    $ui = $item->getUIItem();

                    $inv->getGrid()->remove($ui);
                    $ui->visible = false;

                    $inv->repackInventory();
                }

                $inv->updateMedkitCount();
                break;

            default:
                return;
        }

        $inv->UpdateInventoryStatus();
        $inv->HideUIText();

        $inv->clearSelectedItem();
    }

    public function dropItem(): void
    {
        $inv = $this->inventory;
        $item = $inv->getSelectedItem();

        if (!$item)
        {
            return;
        }

        $ui = $item->getUIItem();

        $inv->DropSound();
        $inv->HideCombobox();

        $inv->getGrid()->remove($ui);
        $ui->visible = false;

        $inv->repackInventory();

        $inv->UpdateInventoryStatus();
        $inv->HideUIText();

        $inv->form('Client')->MainGame->content->SpawnItem();

        $inv->clearSelectedItem();
    }

    public function takeOffItem(): void
    {
        $inv = $this->inventory;
        $item = $inv->getSelectedItem();

        if (!$item)
        {
            return;
        }

        $actor = $inv->form('Client')->MainGame->content->GameActor;

        $actor->takeOffOutfit();

        $inv->addItem('outfit');
        $inv->repackInventory();

        $inv->HideCombobox();
        $inv->HideUIText();
        $inv->DropSound();

        $inv->clearSelectedItem();
    }

    public function putOnItem(): void
    {
        $inv = $this->inventory;
        $item = $inv->getSelectedItem();

        if (!$item)
        {
            return;
        }

        $ui = $item->getUIItem();

        $actor = $inv->form('Client')->MainGame->content->GameActor;

        $inv->getGrid()->remove($ui);
        $ui->visible = false;

        $actor->putOnOutfit();

        $inv->repackInventory();

        $inv->HideCombobox();
        $inv->HideUIText();
        $inv->UseSlotSound();

        $inv->clearSelectedItem();
    }

    public function applyMedkitEffect(): void
    {
        $actor = $this->inventory->form('Client')->MainGame->content->GameActor;

        if ($actor->isDead())
        {
            return;
        }

        $healPct = 20;
        $healAmount = (int)(($actor->getMaxHP() * $healPct) / 100);

        $actor->heal($healAmount);
    }
}