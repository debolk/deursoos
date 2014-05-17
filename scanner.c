/*************************************
*		    scanner.c
**************************************
* waits until card is present, 
* reads ID  and prints it to stdout.
* Program loops until it's killed.
*************************************/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <PCSC/winscard.h>

#define COOL_DOWN_TIME 3

#define CHECK(f, rv) \
 if (SCARD_S_SUCCESS != rv) \
 { \
  fprintf(stderr, f ": %s\n", pcsc_stringify_error(rv)); \
  return -1; \
 }

void scanner_error(char* funcname, char* error)
{
     fprintf(stderr, "%s : %s\n",funcname,  error);
     exit(1);
}


int main(void)
{
    
    LONG rv;

    SCARDCONTEXT hContext;
    DWORD dwReaders = SCARD_AUTOALLOCATE;
       
    LPTSTR mszReaders = NULL;
    SCARDHANDLE hCard;
    DWORD dwActiveProtocol, dwRecvLength;
    SCARD_READERSTATE rgReaderState;
    DWORD cReaders = 1;
    SCARD_IO_REQUEST pioSendPci;
    
    BYTE pbRecvBuffer[258];
    BYTE cmd_get_data[] = { 0xFF, 0xCA, 0x00, 0x00, 0x00};  //GET UID APDU format
    
    unsigned int i;
    DWORD present;


    // init context 
    if((rv = SCardEstablishContext(SCARD_SCOPE_SYSTEM, NULL, NULL, &hContext)) != SCARD_S_SUCCESS)
        scanner_error("SCardEstablishContext", pcsc_stringify_error(rv));


    // get readerslist 
    if((rv = SCardListReaders(hContext, NULL, (LPSTR)&mszReaders, &dwReaders)) != SCARD_S_SUCCESS)
        scanner_error("SCardListReaders" , pcsc_stringify_error(rv));

    //setup state
    //printf("reader name: %s\n", mszReaders);
     rgReaderState.szReader = mszReaders;	
     rgReaderState.dwCurrentState = SCARD_STATE_UNAWARE;	
     rgReaderState.dwEventState = SCARD_STATE_UNAWARE;	
     rgReaderState.cbAtr = MAX_ATR_SIZE;

     //get state
     if((rv = SCardGetStatusChange(hContext, INFINITE, &rgReaderState, cReaders)) != SCARD_S_SUCCESS)
        scanner_error("SCardGetStatusChange" , pcsc_stringify_error(rv));
     
     rgReaderState.dwCurrentState = rgReaderState.dwEventState;	

     while (1) {

        if((rv = SCardGetStatusChange(hContext, INFINITE, &rgReaderState, cReaders)) != SCARD_S_SUCCESS)
            scanner_error("SCardGetStatusChange" , pcsc_stringify_error(rv));

        present = rgReaderState.dwEventState & SCARD_STATE_PRESENT;
        if (!present) { //loop if no card present
            rgReaderState.dwCurrentState = rgReaderState.dwEventState;	
            continue;
        }
		
		//print attributes
		for(i=0; i<rgReaderState.cbAtr; i++) 
          	printf("%02X", rgReaderState.rgbAtr[i]);
          	
         printf("-");
		
         rv = SCardConnect(	hContext, 
         					mszReaders, 
         					SCARD_SHARE_SHARED,
        					(SCARD_PROTOCOL_T0 | SCARD_PROTOCOL_T1), 
        					&hCard, 
        					&dwActiveProtocol);
         if(rv != SCARD_S_SUCCESS)
         	scanner_error("SCardConnect", pcsc_stringify_error(rv));

         switch(dwActiveProtocol)
         {
          case SCARD_PROTOCOL_T0:
           pioSendPci = *SCARD_PCI_T0;
           break;

          case SCARD_PROTOCOL_T1:
           pioSendPci = *SCARD_PCI_T1;
           break;
         }
         
         dwRecvLength = sizeof(pbRecvBuffer);
         
         //send 'get data' command to receive UID
         rv = SCardTransmit(hCard, 
         					&pioSendPci, 
         					cmd_get_data, 
         					sizeof(cmd_get_data),
          					NULL, 
          					pbRecvBuffer, 
          					&dwRecvLength);
          if(rv != SCARD_S_SUCCESS)
         	scanner_error("SCardTransmit", pcsc_stringify_error(rv)); 					
		 
		 //print response 
         //printf("response: ");
         for(i=0; i<dwRecvLength-2; i++)
         	printf("%02X", pbRecvBuffer[i]);
         printf("\n");
         fflush(stdout);

         rv = SCardDisconnect(hCard, SCARD_LEAVE_CARD);
         if(rv != SCARD_S_SUCCESS)
         	scanner_error("SCardDisconnect", pcsc_stringify_error(rv)); 
         sleep(COOL_DOWN_TIME);
     } 

	//unreachable code!

     free(mszReaders);
     rv = SCardReleaseContext(hContext);
     if(rv != SCARD_S_SUCCESS)
     	scanner_error("SCardReleaseContext", pcsc_stringify_error(rv)); 

     return 0;
    }
