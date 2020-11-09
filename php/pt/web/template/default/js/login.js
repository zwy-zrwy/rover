define(function (require,exports){

	// 激活/关闭提交按钮
	var enabledSubmit = [];
	exports.enabledSubmit = function(){
		var p = this.parentNode;
		if(p.tagName !== 'LI')	p = p.parentNode;
		// 激活/关闭代码
		var form = this.form;
		if(!form) return;
		var submit = form.querySelector('input[type="submit"]');
		if(submit){
			form.elements.forEach(function(element,index){
				if(element.name){//表单数据
					enabledSubmit[index] = !form.data[element.name].trim() ? 0 : 1;	
				}
			});
			submit.disabled = enabledSubmit.indexOf(0) >= 0 ? true : false;
		}
	};

	// 输入框小叉子
	function inputEmpty(){
		this.setAttribute('empty',this.value ? 'false' : 'true');
		var clear = this.parentNode.querySelector('.clear');
		if(this.value){
			if(!clear){
				clear = document.createElement('clear');
				clear.className = 'clear';
				clear.innerHTML = '\u00d7';
				if(this.parentNode.tagName !== 'LI')
				clear.style.left = (this.offsetWidth - 28) + 'px';
				this.parentNode.appendChild(clear);
			}
			clear.classList.add('visible');
		}else{
			if(clear)
			clear.classList.remove('visible');
		};
		clear && clear.addEventListener('tap',inputEmpty.clear);
		exports.enabledSubmit.call(this);
	};
	inputEmpty.clear = function(){
		var filed = this.parentNode.firstElementChild,suggestion = this.parentNode.querySelector('.suggestion');
		this.classList.remove('visible');
		this.removeEventListener('tap',inputEmpty.clear);	
		filed.value = '';
		filed.focus();
		if(suggestion && suggestion.classList.contains('visible'))
		suggestion.classList.remove('visible')	
	};
    
    //为页面每一个元素添加清除事件
	document.querySelectorAll('input[type="text"],input[type="tel"],input[type="e-mail"],input[type="password"]').forEach(function(input){
		input.addEventListener('focus',inputEmpty);
		input.addEventListener('input',inputEmpty);
		inputEmpty.call(input);
	});
})