(function() {  
    CKEDITOR.dialog.add("multiimg",  
        function(a) { 
            var ROOT_PATH = "";    // your root path 
            return {  
                title: "批量上传图片",  
                minWidth: "660px",  
                minHeight:"400px",  
                contents: [{  
                    id: "tab1",  
                    label: "",  
                    title: "",  
                    expand: true,  
                    width: "420px",  
                    height: "300px",  
                    padding: 0,  
                    elements: [{  
                        type: "html",  
                        style: "width:660px;height:400px",  
                        html: '<iframe id="uploadFrame" src="'+ROOT_PATH+'ckeditor/plugins/multiimg/dialogs/image.html?v=' +new Date().getSeconds() + '" frameborder="0"></iframe>'  
                    }]  
                }],  
                onOk: function() {
                    var ins = a;  
                    var num = window.imgs.length;
                    var imgHtml = "";
                    for(var i=0;i<num;i++){  
                        imgHtml += "<p><img src=\"" + window.imgs[i] + "\" /></p>";   
                    }
                    ins.insertHtml(imgHtml);   
                },  
                onShow: function () {  
                    document.getElementById("uploadFrame").setAttribute("src",ROOT_PATH+"ckeditor/plugins/multiimg/dialogs/image.html?v=' +new Date().getSeconds() + '");  
                }  
            }  
        })  
})();  