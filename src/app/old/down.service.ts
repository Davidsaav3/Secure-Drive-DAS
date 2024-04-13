import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class DownService {
  constructor(private http: HttpClient) {}

  downloadFile(fileName: string): Observable<any> {
    const url = `https://dasapp.alwaysdata.net/dameUsuario`
    const formData = new FormData();
    formData.append('usuario', 'David');
    return this.http.post(url, formData, { responseType: 'blob' }).pipe(
      catchError((error: HttpErrorResponse) => {
        //console.error('Error al descargar el archivo:', error);
        return throwError('No se pudo descargar el archivo; inténtalo de nuevo más tarde.');
      })
    );
  }

  getFileView(fileName: string): Observable<string> {
    return this.downloadFile(fileName).pipe(
      catchError((error: any) => {
        //console.error('Error al descargar el archivo:', error);
        return throwError('No se pudo obtener el archivo; inténtalo de nuevo más tarde.');
      }),
      map((response: any) => {
        const contentType = response.type;
        const blob = new Blob([response], { type: contentType });
        return window.URL.createObjectURL(blob);
      })
    );
  }
}
