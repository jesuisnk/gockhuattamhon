function tag(e, t) {
  if (document.selection)
    document.form.msg.focus(),
      (document.form.document.selection.createRange().text =
        e + document.form.document.selection.createRange().text + t);
  else if (null != document.forms.form.elements.msg.selectionStart) {
    var n = document.forms.form.elements.msg,
      o = n.value,
      s = n.selectionStart,
      l = n.selectionEnd - n.selectionStart;
    n.value = o.substr(0, s) + e + o.substr(s, l) + t + o.substr(s + l);
  } else document.form.msg.value += e + t;
}
function show_hide(e) {
  (obj = document.getElementById(e)),
    "none" == obj.style.display
      ? (obj.style.display = "block")
      : (obj.style.display = "none");
}
