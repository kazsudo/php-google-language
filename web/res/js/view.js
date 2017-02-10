jQuery(function($){
  if(!$('#list .token').length){
    return false;
  }
  var list = $('#list'), tree = $('#tree');
  list.find('.token').each(function(i, obj){
    var index = parseInt($(obj).attr('data-index')), headTokenIndex = parseInt($(obj).attr('data-headTokenIndex'));
    if(index != headTokenIndex){
      var headToken = $('#list .token[data-index=' + headTokenIndex + ']');
      if(index > headTokenIndex){
        $(obj).appendTo(headToken);
      }
      else {
        $(obj).insertBefore($(headToken).find(' > .content'));
      }
    }
  });

  function showTree(){
    var sid, sentence;
    if(location.hash.match(/#(tree-[0-9]+)/)){
      sid = RegExp.$1.replace(/^tree\-/, 'sentence-');
      sentence = $('#' + sid);
    }
    if(!sentence){
      return false;
    }
    list.find('.token.on').removeClass('on');
    sentence.clone().appendTo(tree);
    list.hide();
    tree.show();
    tree.find('.token:has(.token)').each(function(){
      var tokens = $('<div class="tokens" />').appendTo($(this));
      $(this).find('> .token').appendTo(tokens);
    });
    tree.find('.content').each(function(){
      var content = $(this), headTokenContent, token = content.parent(), index = parseInt(token.attr('data-index')), headTokenIndex = parseInt(token.attr('data-headTokenIndex')), x1, y1, x2, y2, t, l, w, h, svg, line;
      content.prependTo(token);
      if(index != headTokenIndex){
        headTokenContent = $('#tree .token[data-index=' + headTokenIndex + '] > .content');
        x1 = Math.ceil(headTokenContent.offset().left + (headTokenContent.width() / 2));
        y1 = Math.ceil(headTokenContent.offset().top + headTokenContent.height());
        x2 = Math.ceil(content.offset().left + (content.width() / 2));
        y2 = Math.ceil(content.offset().top);
        w = Math.ceil(Math.abs(x1 - x2));
        h = Math.ceil(Math.abs(y1 - y2));
        w = (w > 0 ? w : 1);
        h = (h > 0 ? h : 1);
        t = y1;
        l = (x1 > x2 ? x2 : x2 - w);
        svg = $(document.createElementNS('http://www.w3.org/2000/svg','svg')).attr({'width':w + 'px','height':h + 'px'}).css({'top':t + 'px', 'left':l + 'px'}).appendTo(tree);
        line = $(document.createElementNS('http://www.w3.org/2000/svg','line')).attr({'x1':(x1 <= x2 ? 0 : w), 'y1':0, 'x2':(x1 > x2 ? 0 : w), 'y2':h}).appendTo(svg);
      }
    });
  }
  showTree();
  function closeTree(){
    tree.hide().find('svg, .sentence').remove();
    list.show();
  }
  function resetHash(){
    if(location.hash.match(/^#tree-([0-9]+)$/)){
      location.hash = '#sentence-' + RegExp.$1;
    }
  }

  list.find('.token .content').on('mouseenter mouseleave', function(){
    if($('#tree').is(':visible')){
      return false;
    }
    $(this).parent().toggleClass('on');
  });
  list.find('.sentence').click(function(e){
    location.hash = $(this).attr('id').replace(/^sentence\-/, 'tree-');
  });
  tree.find('.back').click(function(e){
    history.back();
  });
  $(document).on('keydown', function(e){
    if(e.keyCode == 27){
      if($('#tree').is(':visible')){
        history.back();
      }
    }
  });
  $(window).on("hashchange", function(e) {
    if(location.hash.match(/#tree-[0-9]+/)){
      showTree();
    }
    else {
      if($('#tree').is(':visible')){
        closeTree();
      }
    }
  });
});
