// Simple JS for form UX and client-side validation
document.addEventListener('DOMContentLoaded', function(){
  // Prevent double submits
  document.querySelectorAll('form').forEach(function(f){
    f.addEventListener('submit', function(){
      var btn = f.querySelector('button[type=submit]');
      if (btn) { btn.disabled = true; setTimeout(()=>btn.disabled=false, 1500); }
    });
  });

  // Basic client-side validation for price fields
  document.querySelectorAll('input[name=price]').forEach(function(inp){
    inp.addEventListener('input', function(){
      // allow numbers and decimal separator
      this.value = this.value.replace(/[^0-9\\.]/g,'');
    });
  });

});
