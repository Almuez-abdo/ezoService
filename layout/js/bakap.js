
// Switch Between Login And SignUp

// let select = document.querySelectorAll("section h3 span");
// select.forEach(function(ele){
//     ele.onclick = function(){
//         select.forEach(function(ele){
//             ele.classList.remove("active");
//         });
//         this.classList.add("active");

//     };

// });


//     let state = document.querySelector("form .form-group .state");
//     // let city = state.value;

//     sel_state = function(){
//         state.onclick = function(){ 
//         let city = (state.value);
//         }
        
//     }

    
// console.log(city);



function toggleMenu() {
    var menu = document.getElementById('navLinks');
    menu.classList.toggle('.show');
}


// Back to top button (vanilla, no jQuery needed)
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.querySelector('.back-to-top');
    if (!btn) { return; }
    window.addEventListener('scroll', function() {
        btn.style.display = (window.scrollY > 100) ? 'block' : 'none';
    });
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});


function toggleText() {
    var text = document.getElementById("text");
    var btn = document.getElementById("btn");
    if (text.style.maxHeight === "none") {
        text.style.maxHeight = "510px";
        btn.innerHTML = "قراءة المزيد";
    } else {
        text.style.maxHeight = "none";
        btn.innerHTML = "أخفاء";
    }
}