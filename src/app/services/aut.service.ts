import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private loggedIn = false; // Puedes ajustar esto según tu lógica de autenticación

  constructor() { }
  private apiUrl = 'http://ejemplo.com/api/login'; // Cambia esto por la URL real de tu backend

  login(username: string | undefined, password: string | undefined, fa: string | undefined): Promise<any> {
    const body = { username: username, password: password, fa: fa };

    return fetch(this.apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Error en la solicitud de inicio de sesión.');
      }
      return response.json();
    })
    .then(data => {
      this.loggedIn = true;
      return data;
    })
    .catch(error => {
      console.error('Error:', error);
      throw error;
    });
  }

  logout(): void {
    // Lógica de cierre de sesión; establecer el estado de 'loggedIn' como falso
    this.loggedIn = false;
  }

  isLoggedIn(): boolean {
    return this.loggedIn;
  }
}