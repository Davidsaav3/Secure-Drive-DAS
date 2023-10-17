import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../services/aut.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./../app.component.css']
})
export class LoginComponent {
  private apiUrl = 'http://ejemplo.com/api/login'; // Cambia esto por la URL real de tu backend
  constructor(private router: Router, private authService: AuthService) { }
  
  username: string | undefined;
  password: string | undefined;
  fa: string | undefined;

  login(): Promise<any> {
    return this.authService.login(this.username, this.password, this.fa)
      .then(data => {
        if (data == true) {
          this.router.navigate(['/home']);
        }
      })
      .catch(error => {
      });
  }

}