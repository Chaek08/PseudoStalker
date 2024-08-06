<?php
namespace app\forms;

use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\paint\UXColor;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_role_e extends AbstractForm
{
    function ApplyAll()
    {    
        $this->ApplyDeRoleColor();
        $this->ApplyDeRoleIcon();
        $this->ApplyDeRoleName();
        $this->ApplyPidorasRoleColor();
        $this->ApplyPidorasRoleIcon();
        $this->ApplyPidorasRoleName();
    }
    function ResetAll()
    {    
        $this->ResetDeRoleColor();
        $this->ResetDeRoleIcon();
        $this->ResetDeRoleName();
        $this->ResetPidorasRoleColor();
        $this->ResetPidorasRoleIcon();
        $this->ResetPidorasRoleName();
        $this->ResetLadcegaRoleColor();
        $this->ResetLadcegaRoleIcon();
        $this->ResetLadcegaRoleName();
    }
    function ClearAll()
    {    
        $this->ClearDeRoleName();
        $this->ClearDeRoleIcon();
        $this->ClearLaRoleName();
        $this->ClearLaRoleIcon();
        $this->ClearPidoRoleName();
        $this->ClearPidoRoleIcon();
    }    
    /**
     * @event ApplyDeRoleColor_Btn.click-Left 
     */
    function ApplyDeRoleColor(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_dlg->content->community_actor->textColor = $this->Edit_RoleColor_Danila->value;
        $this->form('maingame')->fragment_dlg->content->gg_name->textColor = $this->Edit_RoleColor_Danila->value;        
            
        $this->form('maingame')->fragment_dlg->content->actor_label_1->textColor = $this->Edit_RoleColor_Danila->value;
        $this->form('maingame')->fragment_dlg->content->actor_label_3->textColor = $this->Edit_RoleColor_Danila->value;
        $this->form('maingame')->fragment_dlg->content->answer_name->textColor = $this->Edit_RoleColor_Danila->value;
        
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->community->textColor = $this->Edit_RoleColor_Danila->value;
    }
    /**
     * @event ApplyPidoRoleColor_Btn.click-Left 
     */
    function ApplyPidorasRoleColor(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_dlg->content->community_enemy->textColor = $this->Edit_RoleColor_Pido->value;
        $this->form('maingame')->fragment_dlg->content->enemy_name->textColor = $this->Edit_RoleColor_Pido->value;
        
        $this->form('maingame')->fragment_dlg->content->alex_label_1->textColor = $this->Edit_RoleColor_Pido->value;
        $this->form('maingame')->fragment_dlg->content->alex_label_2->textColor = $this->Edit_RoleColor_Pido->value;
        $this->form('maingame')->fragment_dlg->content->alex_label_3->textColor = $this->Edit_RoleColor_Pido->value;
        
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->community->textColor = $this->Edit_RoleColor_Pido->value;
    }
    /**
     * @event ApplyDeRoleName_Btn.click-Left 
     */
    function ApplyDeRoleName(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_dlg->content->community_actor->text = $this->Edit_RoleName_Danila->text;
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->community->text = $this->Edit_RoleName_Danila->text;
    }
    /**
     * @event ApplyPidoRoleName_Btn.click-Left 
     */
    function ApplyPidorasRoleName(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_dlg->content->community_enemy->text = $this->Edit_RoleName_Pido->text;
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->community->text = $this->Edit_RoleName_Pido->text;
    }
    /**
     * @event ApplyDeRoleIcon_Btn.click-Left 
     */
    function ApplyDeRoleIcon(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_dlg->content->community_actor->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Danila->text));
        
        $this->form('maingame')->fragment_dlg->content->actor_label_1->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Danila->text));
        $this->form('maingame')->fragment_dlg->content->actor_label_3->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Danila->text));
        $this->form('maingame')->fragment_dlg->content->answer_name->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Danila->text));
        
        $this->form('maingame')->fragment_pda->content->fragment_stat->content->community->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Danila->text));
    }
    /**
     * @event ApplyPidoRoleIcon_Btn.click-Left 
     */
    function ApplyPidorasRoleIcon(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_dlg->content->community_enemy->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Pido->text));
        
        $this->form('maingame')->fragment_dlg->content->alex_label_1->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Pido->text));
        $this->form('maingame')->fragment_dlg->content->alex_label_2->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Pido->text));
        $this->form('maingame')->fragment_dlg->content->alex_label_3->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Pido->text));
        
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->community->graphic = new UXImageView(new UXImage($this->Edit_RoleIcon_Pido->text));
    }
    /**
     * @event ResetDeRoleColor_Btn.click-Left 
     */
    function ResetDeRoleColor(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleColor_Danila->value = UXColor::of('#cc8033');
    }
    /**
     * @event ResetPidoRoleColor_Btn.click-Left 
     */
    function ResetPidorasRoleColor(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleColor_Pido->value = UXColor::of('#16a4cd');
    }
    /**
     * @event ResetLaRoleColor_Btn.click-Left 
     */
    function ResetLadcegaRoleColor(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleColor_Ladcega->value = UXColor::of('#e64d4d');
    }
    /**
     * @event ResetDeRoleName_Btn.click-Left 
     */
    function ResetDeRoleName(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleName_Danila->text = $this->Edit_RoleName_Danila->promptText;
    }
    /**
     * @event ResetPidoRoleName_Btn.click-Left 
     */
    function ResetPidorasRoleName(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleName_Pido->text = $this->Edit_RoleName_Pido->promptText;
    }
    /**
     * @event ResetLaRoleName_Btn.click-Left 
     */
    function ResetLadcegaRoleName(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleName_Ladcega->text = $this->Edit_RoleName_Ladcega->promptText;       
    }
    /**
     * @event ResetDeRoleIcon_Btn.click-Left 
     */
    function ResetDeRoleIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleIcon_Danila->text = $this->Edit_RoleIcon_Danila->promptText;
    }
    /**
     * @event ResetPidoRoleIcon_Btn.click-Left 
     */
    function ResetPidorasRoleIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleIcon_Pido->text = $this->Edit_RoleIcon_Pido->promptText;
    }
    /**
     * @event ResetLaRoleIcon_Btn.click-Left 
     */
    function ResetLadcegaRoleIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleIcon_Ladcega->text = $this->Edit_RoleIcon_Ladcega->promptText;
    }
    /**
     * @event ClearDeRoleName_Btn.click-Left 
     */
    function ClearDeRoleName(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleName_Danila->text = '';
    }
    /**
     * @event ClearPidoRoleName_Btn.click-Left 
     */
    function ClearPidoRoleName(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleName_Pido->text = '';
    }
    /**
     * @event ClearLaRoleName_Btn.click-Left 
     */
    function ClearLaRoleName(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleName_Ladcega->text = '';
    }
    /**
     * @event ClearDeRoleIcon_Btn.click-Left 
     */
    function ClearDeRoleIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleIcon_Danila->text = '';
    }
    /**
     * @event CleaarPidoRoleIcon_Btn.click-Left 
     */
    function ClearPidoRoleIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleIcon_Pido->text = '';
    }
    /**
     * @event ClearLaRoleIcon_Btn.click-Left 
     */
    function ClearLaRoleIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_RoleIcon_Ladcega->text = '';
    }
}
