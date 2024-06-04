<?php
namespace app\forms;

use php\gui\UXImage;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_inv_e extends AbstractForm
{
    /**
     * @event ResetAll_Btn.click-Left 
     */
    function ResetAll(UXMouseEvent $e = null)
    {    
        $this->ResetOutfitName();
        $this->ResetOutfitDesc();
        $this->ResetOutfitPrice();
        $this->ResetOutfitWeight();
        $this->ResetOutfitIcon();
        $this->ResetVodkaName();
        $this->ResetVodkaDesc();
        $this->ResetVodkaPrice();
        $this->ResetVodkaWeight();
        $this->ResetVodkaIcon();
    }
    /**
     * @event ApplyAll_Btn.click-Left 
     */
    function ApplyAll(UXMouseEvent $e = null)
    {
        $this->ApplyOutfitIcon();
        $this->ApplyVodkaIcon();
    }    
    /**
     * @event ResetOutfitName_Btn.click-Left 
     */
    function ResetOutfitName(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemName_Outfit->text = $this->Edit_ItemName_Outfit->promptText;
    }
    /**
     * @event ResetOutfitDesc_Btn.click-Left 
     */
    function ResetOutfitDesc(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemDesc_Outfit->text = $this->Edit_ItemDesc_Outfit->promptText;
    }
    /**
     * @event ResetVodkaName_Btn.click-Left 
     */
    function ResetVodkaName(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemName_Vodka->text = $this->Edit_ItemName_Vodka->promptText;
    }
    /**
     * @event ResetVodkaDesc_Btn.click-Left 
     */
    function ResetVodkaDesc(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemDesc_Vodka->text = $this->Edit_ItemDesc_Vodka->promptText;
    }
    /**
     * @event ResetOutfitCount_Btn.click-Left 
     */
    function ResetOutfitPrice(UXMouseEvent $e = null)
    {    
        $this->Edit_Count_Outfit->text = $this->Edit_Count_Outfit->promptText;
    }
    /**
     * @event ResetOutfitWeight_Btn.click-Left 
     */
    function ResetOutfitWeight(UXMouseEvent $e = null)
    {    
        $this->Edit_Weight_Outfit->text = $this->Edit_Weight_Outfit->promptText;
    }
    /**
     * @event ResetVodkaCount_Btn.click-Left 
     */
    function ResetVodkaPrice(UXMouseEvent $e = null)
    {    
        $this->Edit_Count_Vodka->text = $this->Edit_Count_Vodka->promptText;
    }
    /**
     * @event ResetVodkaWeight_Btn.click-Left 
     */
    function ResetVodkaWeight(UXMouseEvent $e = null)
    {    
        $this->Edit_Weight_Vodka->text = $this->Edit_Weight_Vodka->promptText;
    }
    /**
     * @event ResetOutfitIcon_Btn.click-Left 
     */
    function ResetOutfitIcon(UXMouseEvent $e = null)
    {
        $this->Edit_ItemIcon_Outfit->text = $this->Edit_ItemIcon_Outfit->promptText;
        $this->ApplyOutfitIcon();
    }
    /**
     * @event ResetVodkaIcon_Btn.click-Left 
     */
    function ResetVodkaIcon(UXMouseEvent $e = null)
    {
        $this->Edit_ItemIcon_Vodka->text = $this->Edit_ItemIcon_Vodka->promptText;
        $this->ApplyVodkaIcon();
    }
    /**
     * @event ApplyOutfitIcon_Btn.click-Left 
     */
    function ApplyOutfitIcon(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_inv->content->inv_maket_select->image = new UXImage($this->Edit_ItemIcon_Outfit->text);
    }
    /**
     * @event ApplyVodkaIcon_Btn.click-Left 
     */
    function ApplyVodkaIcon(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_inv->content->inv_maket_select_2->image = new UXImage($this->Edit_ItemIcon_Vodka->text);        
    }
}
