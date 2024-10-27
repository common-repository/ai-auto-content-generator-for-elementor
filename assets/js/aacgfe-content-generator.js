
if (typeof (jQuery) != 'undefined') {
    const { __ } = wp.i18n;
    (function ($) {
        'use strict';
        class HandleAll {
            constructor(controlView) {
                this.controls = $(controlView).get(0).$el.parentsUntil('.elementor-controls-stack');
                this.editor = this.controls.find('.elementor-wp-editor');
            }
            genearteContentHandler() {
                Swal.fire({
                    title: 'AI Content Generator',
                    html: `<div class="accg_loader_div"><div class="accg_loader_img"></div></div>`,
                    footer: '<div class="accg_popup_main_div"<div class="aacgfe_popup_button"><button id="copyButton" class="aacgfe_popup_copy_button button button-success">Copy</button><button class="aacgfe_popup_insert_button button button-success">Insert</button><button class="aacgfe_popup_regenerate_button button button-danger" id="ai_regenrate_button">Regenerate</button></div><div class="aacgfe_popbox_tokendata" style="display: none;"><span><h6>Used Tokens</h6><p class="aacgfe_token_use"></p></span></div></div>',
                    input: 'textarea',
                    inputAttributes: {
                        readonly: true
                    },
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                    customClass: "aacgfe-modal-main-wrp",
                    didOpen: () => {
                        const b = Swal.getHtmlContainer().querySelector('.accg_loader_img');
                        b.textContent = Swal.showLoading();
                    },
                  });
                var self = this;
                self.editor.trigger('change');
                var editorId = self.editor.attr('id');
                var activeEditor = window.parent.tinyMCE.get(editorId);
                const content_source = self.controls.find('select[data-setting="content-source"').val();
                if(content_source=='keyword'){
                    var content = self.controls.find('textarea[data-setting="title"').val(); 
                }else{
                    var content = activeEditor.getContent();
                }
                const promptControl = self.controls.find('select[data-setting="prompt-list"]');
                const prompt_txt = promptControl.find(":selected").text();
                const prompt = promptControl.val();
                jQuery('.aacgfe-modal-main-wrp textarea.swal2-textarea').hide();
                jQuery('.accg_popup_main_div').hide();
                self.sendRequest(prompt, content,prompt_txt);
                jQuery("#ai_regenrate_button").on('click',function(){
                        if(window.accg_regenrate){
                        jQuery('.aacgfe_popbox_tokendata').hide();
                        jQuery('.aacgfe-modal-main-wrp textarea.swal2-textarea').hide();
                        jQuery('.accg_loader_div').show();
                        jQuery('.accg_loader_img').textContent=Swal.showLoading();
                        self.sendRequest(prompt, content,prompt_txt);
                        window.accg_regenrate=false;
                    }
                    });
                window.paragraphbox=jQuery('.elementor-element-edit-mode.elementor-widget-text-editor.elementor-element-editable').attr('data-id');
            }
            sendRequest(prompt, content,prompt_txt) {
                jQuery(".swal2-container").on('click',function(event){
                    event.stopPropagation();
                });
                var self = this;
                var requestData = {
                    'action': 'aacgfe_generate_content',
                    'prompt': prompt,
                    'content': content,
                    'prompt_txt':prompt_txt,
                    '_ajax_nonce':ai_ajax_object.aacgfe_nonce,
                   
                };
            $.ajax({
                    url: ai_ajax_object.ajax_url,
                    type: 'POST',
                    data: requestData,
                    dataType: "json",
                    beforeSend: function () {
                    },
                    success: function (response) {
                        window.accg_regenrate=true;
                        if(response.success != null || response.error != null){
                            if(response.success != null){
                                jQuery('.accg_loader_div').hide();
                                jQuery('.aacgfe-modal-main-wrp textarea.swal2-textarea').show();
                                jQuery('.aacgfe_popup_copy_button').show();
                                jQuery('.aacgfe_popup_insert_button').show();
                                jQuery('.accg_popup_main_div').show();
                                jQuery('.aacgfe_popbox_tokendata').show();
                                jQuery('.aacgfe_token_use').html(response.tokens['compeletion']);
                                self.handleGenerateResponse(response.success);
                            }else{
                                if((response.error == 'The server had an error while processing your request. Sorry about that!') || (response.error=='You exceeded your current quota, please check your plan and billing details.')){
                                    jQuery('.aacgfe-modal-main-wrp textarea.swal2-textarea').show();
                                    jQuery('.aacgfe-modal-main-wrp .swal2-textarea').val(response.error);
                                    jQuery('.aacgfe_popup_button').show();
                                    jQuery('.aacgfe_popup_insert_button').hide();
                                    jQuery('.aacgfe_popup_copy_button').hide();
                                    jQuery('.aacgfe_popbox_tokendata').hide();

                                }else{
                                    jQuery('.aacgfe_popup_button').hide();
                                }
                                jQuery('.aacgfe-modal-main-wrp .swal2-textarea').val(response.error);
                                jQuery('.swal2-loader').hide();
                                jQuery('.accg_loader_div').hide();
                                jQuery('.accg_popup_main_div').show();
                            }
                        }else{
                            self.sendRequest(requestData.prompt, requestData.content,requestData.prompt_txt);
                        }
                    },
                    complete: function () {
                    }
                })

            }
            handleGenerateResponse(response) {
                var self = this;
                if (response.indexOf('Error!') == 0) {
                    alert(response);
                    return;
                }
                jQuery('.swal2-loader').hide();
                var editorId = self.editor.attr('id');
                var activeEditor = window.parent.tinyMCE.get(editorId);
                var content = response.trim().replace(/\r?\n/g, '<br />');
                jQuery('.aacgfe-modal-main-wrp .swal2-textarea').val(content);
                jQuery('.aacgfe-modal-main-wrp button.aacgfe_popup_insert_button').on('click',function(){
                    var content = response.trim().replace(/\r?\n/g, '<br />');
                    if (activeEditor !== null) {
                        var content = response.trim().replace(/\r?\n/g, '<br />');
                        activeEditor.setContent(content, { format: 'html' });
                        activeEditor.fire('change');
                        jQuery('.swal2-container.swal2-center.swal2-backdrop-show').hide();
                    }
                    Swal.fire({
                    icon: 'success',
                        title:'Your text has been successfully added to the desired location!',
                        showConfirmButton: true,
                    }
                      )
                    jQuery(".swal2-container").on('click',function(event){
                        event.stopPropagation();
                    });
                });
                    jQuery('#copyButton').on('click',function(){
                    let copy_data= jQuery('.aacgfe-modal-main-wrp .swal2-textarea').val();
                    navigator.clipboard.writeText(copy_data);
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied successfully! Use Ctrl+V or right-click + paste to use(Elementor paste does not work)',
                       showConfirmButton: true,
                    });
                    jQuery(".swal2-container").on('click',function(event){
                        event.stopPropagation();
                    });
                });
        }
    }
    $(window).on("elementor/frontend/init", function () {
        elementor.channels.editor.on('ai:content:generate', function (controlView) {
            const obj = new HandleAll(controlView);
            obj.genearteContentHandler();
        });
    });
    })(jQuery);
}
