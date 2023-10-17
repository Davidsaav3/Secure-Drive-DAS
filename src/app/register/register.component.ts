import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-register',
  templateUrl: 'register.component.html',
  styleUrls: ['./../app.component.css']
})
export class RegisterComponent {
  constructor(private router: Router) { }

  private apiUrl = 'http://ejemplo.com/api/register'; // Cambia esto por la URL real de tu backend
  private apiUrl2 = 'http://ejemplo.com/api/comp'; // Cambia esto por la URL real de tu backend

  mostrar: any= false;
  username: string | undefined;
  email: string | undefined;
  password: string | undefined;
  confirmPassword: string | undefined;
  fa: string | undefined;

  register(): Promise<any> {
    const body = { username: this.username, email: this.email, confirmPassword: this.confirmPassword, password: this.password };

    return fetch(this.apiUrl, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},body: JSON.stringify(body)})
    .then(response => {
      if (!response.ok) {
        throw new Error('Error en la solicitud de inicio de sesión.');
      }
      return response.json();
    })
    .then(data => {
      if(data==true){
        this.mostrar= true;
      }
      return data;
    })
    .catch(error => {
      console.error('Error:', error);
      throw error;
    });
  }

  comp(): Promise<any> {
    const body = { fa: this.fa };

    return fetch(this.apiUrl2, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},body: JSON.stringify(body)})
    .then(response => {
      if (!response.ok) {
        throw new Error('Error en la solicitud de inicio de sesión.');
      }
      return response.json();
    })
    .then(data => {
      if(data==true){
        this.router.navigate(['/home']);
      }
      return data;
    })
    .catch(error => {
      console.error('Error:', error);
      throw error;
    });
  }
}