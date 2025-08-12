var AppOptus = function() {
    this.isLoaded = ko.observable(false);

    var init = function() {};
    var bind = function(Obj) {
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

var UserLogin = function() {
  var self = this;

  this.UserName = ko.observable();
  this.Password = ko.observable();

  this.Login = function() {
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

  this.LoginAD = async function() {
    swal({
      title: "Seleccione una opción",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "AD TLC",
      cancelButtonText: "AD LG",
      closeOnConfirm: true,
      closeOnCancel: true
    }, function(isConfirm) {
      window.location.href = isConfirm
        ? '/ad/login/TLC'
        : '/ad/login/LG';
    });
  };
};


// Si tu lógica de window.onload sólo servía para «recuperar» la cookie,
// puedes simplificarla o eliminarla:
window.onload = function() {
  const data = localStorage.getItem('userdata');
  if (data) {
    localStorage.removeItem('userdata');
    const user = JSON.parse(data);
    User.SetValues(user);
    // No hace falta volver a setear la cookie en JS
    window.location.href = '/dashboard';
  }
};


var User = function() {
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

    var SetValue = function(data) {
        _default = $.extend(_default, data);
        localStorage.setItem('User', JSON.stringify(_default));
    };

    var getData = function() {
        if (localStorage.getItem('User') === null) {
            return {};
        }
        return JSON.parse(localStorage.getItem('User'));
    };

    var logOut = function() {
        Services.Post('/logout', {
                UserToken: User.Token
            },
            (response) => {
                if (response.success) {
                    localStorage.clear();
                    window.location.href = '/login';
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