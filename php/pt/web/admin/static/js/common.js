/*清空缓存*/
function clearCache()
{
  $.ajax({
    type: 'POST',
    url: 'index.php?act=index&op=clear_cache',
    success: function(data){
      if(data){
        layer.msg('更新站点缓存成功');
      }else{
        layer.msg('更新站点缓存失败');
      }
    }
  });
}

/*图片删除*/
function imgRemove(type,id)
{
	var type = type;
	var id= id;
	$.ajax({
    type: 'POST',
    data: {type,id},
    url: 'index.php?act=ajax&op=img_remove',
    dataType: 'json',
    success: function(data){
    	if(data.error==0){
			layer.msg('删除成功');
    		var clas='J_'+data.type+'_'+data.id;
    		$("."+clas).remove();
    	}else{
			layer.msg('删除失败');
    	}
    }
  });
}



function toggle(obj, op, id)
{
  var url = location.href.lastIndexOf("&") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("&"));
  var val = (obj.className.match(/fa-toggle-on/i)) ? 0 : 1;
  $.ajax({
    type: 'POST',
    data: {id,val},
    url: url+'&op='+op,
    dataType: 'json',
    success: function(data){
      if (data.message)
      {
		layer.msg(data.message);
      }
      if (data.error == 0)
      {
        obj.className = (data.content > 0) ? 'fa fa-toggle-on' : 'fa fa-toggle-off';
      }
    },
  });
}
function edit(obj, op, id ,act)
{
  var url = location.href.lastIndexOf("&") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("&"));
  var tag = obj.firstChild.tagName;

  if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
  {
    return;
  }

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = obj.innerText

  /* 创建一个输入框 */
  var txt = document.createElement("INPUT");
  txt.value = (val == 'N/A') ? '' : val;
  txt.setAttribute("class","edit2");
  /* 隐藏对象中的内容，并将输入框加入到对象中 */
  obj.innerHTML = "";
  obj.appendChild(txt);
  txt.focus();
   /* 编辑区失去焦点的处理函数 */
  txt.onblur = function(e)
  {
    if ((txt.value).length > 0 && org != txt.value)
    {
      val = txt.value;
      $.ajax({
        type: 'POST',
        data: {id,val},
        url: url+'&op='+op,
        dataType: 'json',
        success: function(data){
          obj.innerHTML = (data.error == 0) ? data.content : org;

        }
      });
    }
    else
    {
      obj.innerHTML = org;
    }
  }
}

function remove(id, op)
{
  var url = location.href.lastIndexOf("&") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("&"));
  layer.confirm('您确认要删除吗？', {
    btn: ['确认','取消'] //按钮
  }, function(){
    $.ajax({
      type: 'POST',
      data: {id},
      url: url+'&op='+op,
      dataType: 'json',
      success: function(data){
        if (data.error)
        {
          layer.msg(data.message);
        }else{
          layer.msg('操作成功');
          $("#remove_"+data.content).remove();
        }
      },
    });
  });
}

function checkAll(check,name){
  
  if(check.checked){   
      //$("#list :checkbox").prop("checked", true);
    $("input[type='checkbox']").prop("checked","true");  
  }else{   
    $("input[type='checkbox']").removeAttr("checked");
    //$("#list :checkbox").prop("checked", false);
  }
}
