<?php
namespace app\forms;

use action\Element;
use php\gui\UXImage;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_inv_e extends AbstractForm
{
    function ApplyAll()
    {
        $this->ApplyOutfitIcon();
        $this->ApplyVodkaIcon();
    }
    function ResetAll()
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
    function ClearAll()
    {    
        $this->ClearOutfitName();
        $this->ClearOutfitDesc();
        $this->ClearOutfitCount();
        $this->ClearOutfitWeight();
        $this->ClearOutfitIcon();
        $this->ClearVodkaName();
        $this->ClearVodkaDesc();
        $this->ClearVodkaCount();
        $this->ClearVodkaWeight();
        $this->ClearVodkaIcon();
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
        $this->Edit_ItemDesc_Vodka->text = $this->Default_ItemDesc_Vodka->text;
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
    }
    /**
     * @event ResetVodkaIcon_Btn.click-Left 
     */
    function ResetVodkaIcon(UXMouseEvent $e = null)
    {
        $this->Edit_ItemIcon_Vodka->text = $this->Edit_ItemIcon_Vodka->promptText;
    }
    /**
     * @event ApplyOutfitIcon_Btn.click-Left 
     */
    function ApplyOutfitIcon(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Inventory->content->inv_maket_select->image = new UXImage($this->Edit_ItemIcon_Outfit->text);
    }
    /**
     * @event ApplyVodkaIcon_Btn.click-Left 
     */
    function ApplyVodkaIcon(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Inventory->content->inv_maket_select_2->image = new UXImage($this->Edit_ItemIcon_Vodka->text);        
    }
    /**
     * @event ClearOutfitName_Btn.click-Left 
     */
    function ClearOutfitName(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemName_Outfit->text = '';
    }
    /**
     * @event ClearOutfitIcon_Btn.click-Left 
     */
    function ClearOutfitIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemIcon_Outfit->text = '';
    }
    /**
     * @event ClearOutfitDesc_Btn.click-Left 
     */
    function ClearOutfitDesc(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemDesc_Outfit->text = '';
    }
    /**
     * @event ClearVodkaName_Btn.click-Left 
     */
    function ClearVodkaName(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemName_Vodka->text = '';
    }
    /**
     * @event ClearVodkaIcon_Btn.click-Left 
     */
    function ClearVodkaIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemIcon_Vodka->text = '';
    }
    /**
     * @event ClearVodkaDesc_Btn.click-Left 
     */
    function ClearVodkaDesc(UXMouseEvent $e = null)
    {    
        $this->Edit_ItemDesc_Vodka->text = '';
    }
    /**
     * @event ClearOutfitCount_Btn.click-Left 
     */
    function ClearOutfitCount(UXMouseEvent $e = null)
    {    
        $this->Edit_Count_Outfit->text = '';
    }
    /**
     * @event ClearOutfitWeight_Btn.click-Left 
     */
    function ClearOutfitWeight(UXMouseEvent $e = null)
    {    
        $this->Edit_Weight_Outfit->text = '';
    }
    /**
     * @event ClearVodkaCount_Btn.click-Left 
     */
    function ClearVodkaCount(UXMouseEvent $e = null)
    {    
        $this->Edit_Count_Vodka->text = '';
    }
    /**
     * @event ClearVodkaWeight_Btn.click-Left 
     */
    function ClearVodkaWeight(UXMouseEvent $e = null)
    {    
        $this->Edit_Weight_Vodka->text = '';
    }
}
