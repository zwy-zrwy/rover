/*
 * Ϊ�Ͱ汾IE���placeholderЧ��
 *
 * ʹ�÷�����
 * [html]
 * <input id="captcha" name="captcha" type="text" placeholder="��֤��" value="" >
 * [javascrpt]
 * $("#captcha").nc_placeholder();
 *
 * ��Ч���ύ��ʱ��placeholder�����ݻᱻ�ύ�����������ύǰ��Ҫ��ֵ���
 * ������
 * $('[data-placeholder="placeholder"]').val("");
 * $("#form").submit();
 *
 */
(function($) {
    $.fn.nc_placeholder = function() {
        var isPlaceholder = 'placeholder' in document.createElement('input');
        return this.each(function() {
            if(!isPlaceholder) {
                $el = $(this);
                $el.focus(function() {
                    if($el.attr("placeholder") === $el.val()) {
                        $el.val("");
                        $el.attr("data-placeholder", "");
                    }
                }).blur(function() {
                    if($el.val() === "") {
                        $el.val($el.attr("placeholder"));
                        $el.attr("data-placeholder", "placeholder");
                    }
                }).blur();
            }
        });
    };
})(jQuery);