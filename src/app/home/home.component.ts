import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../auth.service'; // Ajusta la ruta según la ubicación de tu servicio AuthService

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./../app.component.css']
})

export class HomeComponent  implements OnInit {
  private apiUrl = 'http://ejemplo.com/api/data'; // Cambia esto por la URL real de tu backend
  email: any;

  constructor(private router: Router,private authService: AuthService) { }
  files: any[] = [];
  selectedFile: File | null = null;

  imageList: any[] = [
    { url: 'https://via.placeholder.com/300' },
  ];

  ngOnInit(): void {
    fetch('http://ejemplo.com/api/userdata') // Reemplaza con la URL correcta de tu API
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al obtener los datos del usuario.');
        }
        return response.json();
      })
      .then(data => {
        this.email = data.email;
        this.files = data.files;
      })
      .catch(error => {
        console.error('Error:', error);
      });
  }
  
  logout(): void {
    this.authService.setAuthenticated(true);
  }

  onFileSelected(event: any): void {
    this.selectedFile = event.target.files[0] as File;
  }

  upload(): void {
    if (this.selectedFile) {
      const uploadData = new FormData();
      uploadData.append('file', this.selectedFile, this.selectedFile.name);

      // Aquí puedes usar 'fetch' o algún otro método para subir el archivo
      // Ejemplo con 'fetch':
      fetch('http://ejemplo.com/api/upload', {
        method: 'POST',
        body: uploadData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al cargar archivo.');
        }
        return response.json();
      })
      .then(data => {
        // Manejar la respuesta si es necesario
      })
      .catch(error => {
        console.error('Error:', error);
        throw error;
      });
    }
  }

  deleteDoc(id: number): Promise<any> {
    const deleteUrl = `${this.apiUrl}/${id}`;
    return fetch(deleteUrl, {
      method: 'DELETE'
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Error al eliminar datos.');
      }
      return response.json();
    })
    .catch(error => {
      console.error('Error:', error);
      throw error;
    });
  }


}
