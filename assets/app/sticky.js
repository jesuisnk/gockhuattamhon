var heightCot1 = $('.col-xl-8').height();
var heightCot2 = $('.col-xl-4').height();
if (heightCot1 < heightCot2) {
  $('.col-xl-8 #part1').addClass('add-sticky');
  $('.col-xl-4').addClass('add-sticky');
  $('.col-xl-4 #part2').removeClass('add-sticky');
} else {
  $('.col-xl-4 #part2').addClass('add-sticky');
  $('.col-xl-8').addClass('add-sticky');
  $('.col-xl-8 #part1').removeClass('add-sticky');
}