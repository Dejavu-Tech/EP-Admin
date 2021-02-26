/*e.preventDefault();//阻止元素发生默认的行为(例如,当点击提交按钮时阻止对表单的提交*/

/* 文本框控件 text
acc  是 class="component" 的DIV 
e 是 class="leipiplugins" 的控件
*/
LPB.plugins['text'] = function (active_component,leipiplugins) {
  var plugins = 'text',popover = $(".popover");
  //右弹form  初始化值
  $(popover).find("#orgname").val($(leipiplugins).attr("title"));
  $(popover).find("#orgvalue").val($(leipiplugins).val());
  //右弹form  取消控件
  $(popover).delegate(".btn-danger", "click", function(e){
     
      active_component.popover("hide");
  });
  //右弹form  确定控件
  $(popover).delegate(".btn-info", "click", function(e){
     
      var inputs = $(popover).find("input");
      if($(popover).find("textarea").length>0)
      {
          inputs.push($(popover).find("textarea")[0]);
      }
      $.each(inputs, function(i,e){
          var attr_name = $(e).attr("id");//属性名称
          var attr_val = $(e).val();
          switch(attr_name)
          {
            //要break
            case 'orgvalue':
              //$(leipiplugins).val(attr_val);
              $(leipiplugins).attr("value", attr_val);
              break;
            //没有break
            case 'orgname':
              $(leipiplugins).attr("title",attr_val);
              active_component.find(".leipiplugins-orgname").text(attr_val);
              break;
            default:
              $(leipiplugins).attr(attr_name, attr_val);
          }
          active_component.popover("hide");
          LPB.genSource();//重置源代码
      });

  });
}
/* 多行文本框控件 textarea
acc  是 class="component" 的DIV 
e 是 class="leipiplugins" 的控件
*/
LPB.plugins['textarea'] = function (active_component,leipiplugins) {
  var plugins = 'textarea',popover = $(".popover");
  //右弹form  初始化值
  $(popover).find("#orgname").val($(leipiplugins).attr("title"));
  $(popover).find("#orgvalue").val($(leipiplugins).val());
  //右弹form  取消控件
  $(popover).delegate(".btn-danger", "click", function(e){
     
      active_component.popover("hide");
  });
  //右弹form  确定控件
  $(popover).delegate(".btn-info", "click", function(e){
     
      var inputs = $(popover).find("input");
      if($(popover).find("textarea").length>0)
      {
          inputs.push($(popover).find("textarea")[0]);
      }
      $.each(inputs, function(i,e){
          var attr_name = $(e).attr("id");//属性名称
          var attr_val = $(e).val();
          switch(attr_name)
          {
            //要break
            case 'orgvalue':
              //$(leipiplugins).val(attr_val);
              $(leipiplugins).text(attr_val);
              break;
            //没有break
            case 'orgname': 
              $(leipiplugins).attr("title",attr_val);
              active_component.find(".leipiplugins-orgname").text(attr_val);
            default:
              $(leipiplugins).attr(attr_name, attr_val);
          }
          active_component.popover("hide");
          LPB.genSource();//重置源代码
      });

  });
}
/* 下拉框控件 select
acc  是 class="component" 的DIV 
e 是 class="leipiplugins" 的控件
*/
LPB.plugins['select'] = function (active_component,leipiplugins) {
  var plugins = 'select',popover = $(".popover");
  //右弹form  初始化值
  $(popover).find("#orgname").val($(leipiplugins).attr("title"));
  var val = $.map($(leipiplugins).find("option"), function(e,i){return $(e).text()});
  val = val.join("\r");
  $(popover).find("#orgvalue").text(val);
  //右弹form  取消控件
  $(popover).delegate(".btn-danger", "click", function(e){
      active_component.popover("hide");
  });
  //右弹form  确定控件
  $(popover).delegate(".btn-info", "click", function(e){
     
      var inputs = $(popover).find("input");
      if($(popover).find("textarea").length>0)
      {
          inputs.push($(popover).find("textarea")[0]);
      }
      $.each(inputs, function(i,e){
          var attr_name = $(e).attr("id");//属性名称
          var attr_val = $(e).val();
          switch(attr_name)
          {
            //要break
            case 'orgvalue':
              var options = attr_val.split("\n");
              $(leipiplugins).html("");
              $.each(options, function(i,e){
                $(leipiplugins).append("\n      ");
                $(leipiplugins).append($("<option>").text(e));
              });
              //$(leipiplugins).text(attr_val);
              break;
            
            case 'orgname': 
              $(leipiplugins).attr("title",attr_val);
              active_component.find(".leipiplugins-orgname").text(attr_val);
              break;
            default:
              $(leipiplugins).attr(attr_name, attr_val);
          }
          active_component.popover("hide");
          LPB.genSource();//重置源代码
      });

  });
}

/* 复选控件 checkbox
acc  是 class="component" 的DIV 
e 是 class="leipiplugins" 的控件
*/
LPB.plugins['checkbox'] = function (active_component,leipiplugins) {
  var plugins = 'checkbox',popover = $(".popover");
  //右弹form  初始化值
  $(popover).find("#orgname").val($(leipiplugins).attr("title"));
  val = $.map($(leipiplugins), function(e,i){return $(e).val().trim()});
  val = val.join("\r");
  $(popover).find("#orgvalue").text(val);
  //右弹form  取消控件
  $(popover).delegate(".btn-danger", "click", function(e){
     
      active_component.popover("hide");
  });
  //右弹form  确定控件
  $(popover).delegate(".btn-info", "click", function(e){
     
      var inputs = $(popover).find("input");
      if($(popover).find("textarea").length>0)
      {
          inputs.push($(popover).find("textarea")[0]);
      }
      $.each(inputs, function(i,e){
          var attr_name = $(e).attr("id");//属性名称
          var attr_val = $(e).val();
          switch(attr_name)
          {
            //要break
            case 'orgvalue':
              var checkboxes = attr_val.split("\n");
              var html = "<!-- Multiple Checkboxes -->\n";
              $.each(checkboxes, function(i,e){
                if(e.length > 0){
                  var vName = $(leipiplugins).eq(i).attr("name"),vTitle = $(leipiplugins).eq(i).attr("title"),orginline = $(leipiplugins).eq(i).attr("orginline");
                  if(!vName) vName = ''; if(!vTitle) vTitle = ''; if(!orginline) orginline = '';
                  html += '<label class="checkbox '+orginline+'">\n<input type="checkbox" name="'+vName+'" title="'+vTitle+'" value="'+e+'" orginline="'+orginline+'" class="leipiplugins" leipiplugins="checkbox" >'+e+'\n</label>';
                  }
                $(active_component).find(".leipiplugins-orgvalue").html(html);
              });
              break;
            
            case 'orgname': 
              $(leipiplugins).attr("title",attr_val);
              active_component.find(".leipiplugins-orgname").text(attr_val);
              break;
            default:
              $(leipiplugins).attr(attr_name, attr_val);
          }
          active_component.popover("hide");
          LPB.genSource();//重置源代码
      });

  });
}

/* 复选控件 radio
acc  是 class="component" 的DIV 
e 是 class="leipiplugins" 的控件
*/
LPB.plugins['radio'] = function (active_component,leipiplugins) {
  var plugins = 'radio',popover = $(".popover");
  //右弹form  初始化值
  $(popover).find("#orgname").val($(leipiplugins).attr("title"));
  val = $.map($(leipiplugins), function(e,i){return $(e).val().trim()});
  val = val.join("\r");
  $(popover).find("#orgvalue").text(val);
  //右弹form  取消控件
  $(popover).delegate(".btn-danger", "click", function(e){
     
      active_component.popover("hide");
  });
  //右弹form  确定控件
  $(popover).delegate(".btn-info", "click", function(e){
     
      var inputs = $(popover).find("input");
      if($(popover).find("textarea").length>0)
      {
          inputs.push($(popover).find("textarea")[0]);
      }
      $.each(inputs, function(i,e){
          var attr_name = $(e).attr("id");//属性名称
          var attr_val = $(e).val();
          switch(attr_name)
          {
            //要break
            case 'orgvalue':
              var checkboxes = attr_val.split("\n");
              var html = "<!-- Multiple Checkboxes -->\n";
              $.each(checkboxes, function(i,e){
                if(e.length > 0){
                  var vName = $(leipiplugins).eq(i).attr("name"),vTitle = $(leipiplugins).eq(i).attr("title"),orginline = $(leipiplugins).eq(i).attr("orginline");
                  if(!vName) vName = ''; if(!vTitle) vTitle = ''; if(!orginline) orginline = '';
                  html += '<label class="radio '+orginline+'">\n<input type="radio" name="'+vName+'" title="'+vTitle+'" value="'+e+'" orginline="'+orginline+'" class="leipiplugins" leipiplugins="radio" >'+e+'\n</label>';
                  }
                $(active_component).find(".leipiplugins-orgvalue").html(html);
              });
              break;
            
            case 'orgname': 
              $(leipiplugins).attr("title",attr_val);
              active_component.find(".leipiplugins-orgname").text(attr_val);
              break;
            default:
              $(leipiplugins).attr(attr_name, attr_val);
          }
          active_component.popover("hide");
          LPB.genSource();//重置源代码
      });

  });
}

/* 上传控件 uploadfile
acc  是 class="component" 的DIV 
e 是 class="leipiplugins" 的控件
*/
LPB.plugins['uploadfile'] = function (active_component,leipiplugins) {
  var plugins = 'uploadfile',popover = $(".popover");
  //右弹form  初始化值
  $(popover).find("#orgname").val($(leipiplugins).attr("title"));
  //右弹form  取消控件
  $(popover).delegate(".btn-danger", "click", function(e){
     
      active_component.popover("hide");
  });
  //右弹form  确定控件
  $(popover).delegate(".btn-info", "click", function(e){
     
      var inputs = $(popover).find("input");
      if($(popover).find("textarea").length>0)
      {
          inputs.push($(popover).find("textarea")[0]);
      }
      $.each(inputs, function(i,e){
          var attr_name = $(e).attr("id");//属性名称
          var attr_val = $(e).val();
          switch(attr_name)
          {
            case 'orgname': 
              $(leipiplugins).attr("title",attr_val);
              active_component.find(".leipiplugins-orgname").text(attr_val);
              break;
            default:
              $(leipiplugins).attr(attr_name, attr_val);
          }
          active_component.popover("hide");
          LPB.genSource();//重置源代码
      });

  });
}