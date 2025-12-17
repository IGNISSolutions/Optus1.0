var AppOptus = function () {
  this.isLoaded = ko.observable(false);

  var init = function () { };
  var bind = function (Obj) {
    if (!window.File && !window.FileReader && !window.FileList && !window.Blob) {
      swal('Alerta!', 'Su navegador no tiene soporte para funciones de HTML5. Por favor actualice su navegador.', 'error');
    } else {
      ko.applyBindings(Obj);
      self.isLoaded(true);
      // iCheck pierde el evento 'hover' en los input.icheck que estén
      // anidados en un elemento que tenga bindeado un loop de Knockout.
      // Se soluciona iniciando iCheck después de ko.applyBindings().
      //App.handleiCheck();
    }
  };
  return {
    Init: init,
    Bind: bind
  };
}();

var UserLogin = function () {
  var self = this;

  this.UserName = ko.observable();
  this.Password = ko.observable();

  this.Login = function () {
    const payload = {
      UserName: self.UserName(),
      Password: self.Password()
    };

    // Usamos fetch directamente para poder enviar 'credentials: include'
    fetch('/login/send', {
      method: 'POST',
      credentials: 'include',               // <–– permite recibir la cookie firmada por el servidor
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(response => {
        if (response.success) {
          const user = response.data.user;

          if (user.RequiresIpVerification === 'S' || user.PassChange === 'S') {
            window.location.href = '/verify-code-advice';
          } else {
            User.SetValues(user);
            // ¡OJO! Aquí ya **no** ponemos setCookie():  
            // el servidor nos envió la cookie firmada (HttpOnly) en la cabecera Set-Cookie  
            window.location.href = '/dashboard';
          }
        } else {
          swal('Error', response.message, 'error');
        }
      })
      .catch(error => {
        swal('Error', error.message, 'error');
      });
  };

  this.LoginAD = async function () {
    swal({
      title: "Seleccione una opción",
      text: `
      <div id="login3btns" style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
        <button class="swal3btn" data-href="/ad/login/TLC">Telecentro</button>
        <button class="swal3btn" data-href="/ad/login/LG">Los Grobo</button>
        <button class="swal3btn" data-href="/a0/login/SCR">Sancor Seguros</button>
        <button id="btnBack" class="swal3btn swal3btn-back">Volver</button>
      </div>
    `,
      html: true,
      showCancelButton: false,
      closeOnConfirm: false,
      closeOnClickOutside: false,
      closeOnEsc: false
    });

    // --- Estilos personalizados ---
    (function addCustomStyles() {
      const style = document.createElement("style");
      style.textContent = `
      .sweet-alert .sa-button-container { display: none !important; }
      #login3btns .swal3btn {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
      }
      #login3btns .swal3btn:nth-child(1) { background:#1e3a5f; color:white; }
      #login3btns .swal3btn:nth-child(1):hover { background:#152a45; }
      #login3btns .swal3btn:nth-child(2) { background:#ffffff; color:#b8860b; border-color:#b8860b; }
      #login3btns .swal3btn:nth-child(2):hover { background:#f5f5f5; }
      #login3btns .swal3btn:nth-child(3) { background:#e91e63; color:white; }
      #login3btns .swal3btn:nth-child(3):hover { background:#c2185b; }
      #login3btns .swal3btn-back {
        background:#e5e7eb;
        color:#111827;
      }
      #login3btns .swal3btn-back:hover {
        background:#d1d5db;
      }
    `;
      document.head.appendChild(style);
    })();

    // --- Handlers ---
    setTimeout(() => {
      document.querySelectorAll("#login3btns .swal3btn").forEach(btn => {
        btn.addEventListener("click", e => {
          const href = e.currentTarget.getAttribute("data-href");
          if (href) {
            swal.close();
            window.location.href = href;
          } else {
            swal.close(); // “Volver”
          }
        });
      });
    }, 0);
  };



};


// Si tu lógica de window.onload sólo servía para «recuperar» la cookie,
// puedes simplificarla o eliminarla:
window.onload = function () {
  const data = localStorage.getItem('userdata');
  if (data) {
    localStorage.removeItem('userdata');
    const user = JSON.parse(data);
    User.SetValues(user);
    // No hace falta volver a setear la cookie en JS
    window.location.href = '/dashboard';
  }
};


var User = function () {
  var _default = {
    Token: '',
    Id: '',
    Nombre: '',
    Apellido: '',
    FullName: '',
    Image: '',
    Email: '',
    Tipo: '',
    PassChange: '',
    Permissions: [],
    isAdmin: false,
    isCustomer: false,
    isOfferer: false
  };

  var SetValue = function (data) {
    _default = $.extend(_default, data);
    localStorage.setItem('User', JSON.stringify(_default));
  };

  var getData = function () {
    if (localStorage.getItem('User') === null) {
      return {};
    }
    return JSON.parse(localStorage.getItem('User'));
  };

  var logOut = function () {
    // Verificar si el usuario se logueó con Auth0
    var authProvider = localStorage.getItem('auth_provider');
    
    Services.Post('/logout', {
      UserToken: User.Token
    },
      (response) => {
        if (response.success) {
          localStorage.clear();
          // Si el login fue con Auth0, redirigir al logout de Auth0 para cerrar la sesión SSO
          if (authProvider === 'A0') {
            window.location.href = '/a0/logout';
          } else {
            window.location.href = '/login';
          }
        } else {
          swal('Error.', response.message, 'error');
        }
      },
      (error) => {
        swal('Error.', error.message, 'error');
      },
      null,
      null
    );
  };

  var can = (permission) => {
    var permissions = getData().Permissions;
    return typeof permissions != 'undefined' && permissions.length > 0 ? permissions.some(p => p === permission) : false;
  }

  var cannot = (permission) => {
    var permissions = getData().Permissions;
    return typeof permissions != 'undefined' && permissions.length > 0 ? !permissions.some(p => p === permission) : true;
  }

  return {
    Token: getData().Token,
    Id: getData().Id,
    Nombre: getData().Nombre,
    Apellido: getData().Apellido,
    FullName: getData().FullName,
    Image: getData().Image,
    Email: getData().Email,
    Tipo: getData().Tipo,
    PassChange: getData().PassChange,
    isAdmin: getData().isAdmin,
    isCustomer: getData().isCustomer,
    isOfferer: getData().isOfferer,
    SetValues: SetValue,
    LogOut: logOut,
    can: can,
    cannot: cannot
  };
}();