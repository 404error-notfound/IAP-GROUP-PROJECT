let slideIndex=0;
showSlide();

function showSlide(){
    const slides=document.getElementById("slide");
    const dots=document.getElementById("dots");

    for(let i; i<slides.length; i++){
        slides[i].style.display="none";
    }
    slideIndex++;
    if(slideIndex>slideIndex.length){
        slideIndex=1;
    }
    for(let i=0;i<dots.length;i++){
        dots[i].classList.remove("active");
    }
    slides[slideIndex-1].style.display="block";
    dots[slideIndex-1].classList.add("active");
    setTimeout(showSlide,5000);
}
function currentSlide(n){
    slideIndex=n-1;
    showSlide();
}



