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

int main(void)
{
 LONG rv;

 SCARDCONTEXT hContext;
 LPTSTR mszReaders;
 SCARDHANDLE hCard;
 DWORD dwReaders, dwActiveProtocol, dwRecvLength;
 SCARD_READERSTATE rgReaderState;
 DWORD cReaders = 1;

 SCARD_IO_REQUEST pioSendPci;
 BYTE pbRecvBuffer[258];
 BYTE cmd1[] = { 0xFF, 0xCA, 0x00, 0x00, 0x00};
 
 unsigned int i;
 DWORD present;

 rv = SCardEstablishContext(SCARD_SCOPE_SYSTEM, NULL, NULL, &hContext);
 CHECK("SCardEstablishContext", rv)

 rv = SCardListReaders(hContext, NULL, NULL, &dwReaders);
 CHECK("SCardListReaders", rv)

 mszReaders = calloc(dwReaders, sizeof(char));
 rv = SCardListReaders(hContext, NULL, mszReaders, &dwReaders);
 CHECK("SCardListReaders", rv)
 //printf("reader name: %s\n", mszReaders);

 rgReaderState.szReader = mszReaders;	
 rgReaderState.dwCurrentState = SCARD_STATE_UNAWARE;	
 rgReaderState.dwEventState = SCARD_STATE_UNAWARE;	
 rgReaderState.cbAtr = MAX_ATR_SIZE;


 rv = SCardGetStatusChange(hContext, INFINITE, &rgReaderState, cReaders);
 CHECK("SCardGetStatusChange", rv)
 rgReaderState.dwCurrentState = rgReaderState.dwEventState;	

 while (1) {

 rv = SCardGetStatusChange(hContext, INFINITE, &rgReaderState, cReaders);
 CHECK("SCardGetStatusChange", rv)

 present = rgReaderState.dwEventState & SCARD_STATE_PRESENT;
 if (!present) {
   rgReaderState.dwCurrentState = rgReaderState.dwEventState;	
   continue;
 }
 

 for(i=0; i<rgReaderState.cbAtr; i++)
  printf("%02X", rgReaderState.rgbAtr[i]);
 printf("-");

 rv = SCardConnect(hContext, mszReaders, SCARD_SHARE_SHARED,
 SCARD_PROTOCOL_T0 | SCARD_PROTOCOL_T1, &hCard, &dwActiveProtocol);

 CHECK("SCardConnect", rv)

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
 rv = SCardTransmit(hCard, &pioSendPci, cmd1, sizeof(cmd1),
  NULL, pbRecvBuffer, &dwRecvLength);
 CHECK("SCardTransmit", rv)

 //printf("response: ");
 for(i=0; i<dwRecvLength-2; i++)
  printf("%02X", pbRecvBuffer[i]);
 printf("\n");
 fflush(stdout);

 rv = SCardDisconnect(hCard, SCARD_LEAVE_CARD);
 CHECK("SCardDisconnect", rv)
 sleep(COOL_DOWN_TIME);
 } 

 free(mszReaders);

 rv = SCardReleaseContext(hContext);
 CHECK("SCardReleaseContext", rv)

 return 0;
}
